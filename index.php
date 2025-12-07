<?php
require_once __DIR__ . '/config.php';

function fetch_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent SSL issues on MAMP
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/* -------------------------------------------------
   APOD — Astronomy Picture of the Day
--------------------------------------------------*/
$apod_json = fetch_api("https://api.nasa.gov/planetary/apod?api_key=" . NASA_API_KEY);
$apod = $apod_json ? json_decode($apod_json, true) : null;



// -----------------------------
// Fetch Launches WITH CACHING
// -----------------------------
$cache_file = __DIR__ . '/cache_launches.json';
$cache_lifetime = 300; // 5 minutes

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_lifetime)) {
    // Use cached version if fresh
    $launches_json = file_get_contents($cache_file);
} else {
    // Fetch live data
    $launches_json = fetch_api("https://ll.thespacedevs.com/2.2.0/launch/upcoming/?limit=6");

    // If the fetch worked (valid JSON), store it
    if ($launches_json && strlen($launches_json) > 10) {
        file_put_contents($cache_file, $launches_json);
    }
}

$launches = $launches_json ? json_decode($launches_json, true) : [];
$launch_results = $launches['results'] ?? [];

?>

<?php include __DIR__ . '/partials/header.php'; ?>

<h1 style="margin-bottom: 16px;">Dashboard Overview</h1>
<p style="color: var(--muted); margin-bottom: 24px;">
  A quick snapshot of today’s space data: APOD, Mars rover activity, and upcoming launches.
</p>

<!-- DASHBOARD LAYOUT -->
<div class="dashboard-layout">

    <!-- LEFT: LARGE APOD CARD -->
    <div class="big-card card clickable" onclick="window.location='astronomy.php?date=<?= date('Y-m-d') ?>'">
        <h2>Astronomy Picture of the Day</h2>

        <?php if ($apod && !empty($apod['url'])): ?>
            
            <?php if (($apod['media_type'] ?? '') === 'image'): ?>
                <img src="<?= htmlspecialchars($apod['url']) ?>" alt="APOD">
            <?php else: ?>
                <p>Media is not an image — <strong>click to view APOD</strong></p>
            <?php endif; ?>

            <p><strong><?= htmlspecialchars($apod['title'] ?? '') ?></strong></p>
            <p class="apod-description">
    <?= htmlspecialchars(mb_strimwidth($apod['explanation'] ?? '', 0, 1000, '…')) ?>
</p>
<?php if (!empty($apod['date'])): ?>
    <p style="opacity: 0.7; font-size: 0.9rem;">
        <strong>Date:</strong> <?= htmlspecialchars($apod['date']) ?>
    </p>
<?php endif; ?>

<?php if (!empty($apod['copyright'])): ?>
    <p style="opacity: 0.7; font-size: 0.9rem;">
        <strong>Credit:</strong> <?= htmlspecialchars($apod['copyright']) ?>
    </p>
<?php endif; ?>

        <?php else: ?>
            <p><strong>No APOD data available.</strong></p>
            <p>Click to view APOD directly →</p>
        <?php endif; ?>
    </div>


    <!-- RIGHT STACKED CARDS -->
    <div class="right-cards">
<!--  SMALL CARD: STARS -->
<?php
// Fetch 1 random star image from NASA Image Library
$stars_json = fetch_api("https://images-api.nasa.gov/search?q=stars&media_type=image");
$stars_data = $stars_json ? json_decode($stars_json, true) : null;

$star_items = $stars_data['collection']['items'] ?? [];
$star_img_url = null;

if (!empty($star_items)) {
    // Pick random image
    $random_star = $star_items[array_rand($star_items)];
    $star_img_url = $random_star['links'][0]['href'] ?? null;
}
?>

<div class="small-card card clickable" onclick="window.location='stars.php'">
    <h2>Stars & Nebulae</h2>

    <?php if ($star_img_url): ?>
        <img src="<?= htmlspecialchars($star_img_url) ?>" 
             alt="Star Image" 
             style="width:100%; border-radius:12px; margin-bottom:10px;">
    <?php endif; ?>

    <p><strong>NASA Star Imagery</strong></p>
    <p>Explore deep-space star clusters & cosmic formations.</p>
</div>


        <!-- LAUNCHES -->
        <div class="small-card card clickable" onclick="window.location='launches.php'">
            <h2>Upcoming Launches</h2>

            <?php if (!empty($launch_results)): ?>
                <ul class="launch-list">
                <?php foreach (array_slice($launch_results, 0, 5) as $l): ?>
                    <li>
                        <strong><?= htmlspecialchars($l['name'] ?? '') ?></strong><br>
                        <span><?= htmlspecialchars($l['net'] ?? '') ?></span>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No launch data available.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
