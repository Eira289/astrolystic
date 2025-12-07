<?php
require_once __DIR__ . '/config.php';

function fetch_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Fetch NASA star images (max 30)
$stars_json = fetch_api("https://images-api.nasa.gov/search?q=stars&media_type=image");
$stars_data = $stars_json ? json_decode($stars_json, true) : null;

$star_items = $stars_data['collection']['items'] ?? [];
?>

<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Stars & Nebulae Gallery</h1>
<p style="color: var(--muted); margin-bottom: 20px;">
    A curated NASA collection of star clusters, nebulae, and deep-space objects.
</p>

<?php if (!empty($star_items)): ?>
    <div class="grid grid-3">
        <?php foreach (array_slice($star_items, 0, 18) as $item): ?>
            <?php 
                $img = $item['links'][0]['href'] ?? null;
                $title = $item['data'][0]['title'] ?? "Untitled";
                $desc = $item['data'][0]['description'] ?? "";
                $date = $item['data'][0]['date_created'] ?? "";
            ?>
            <div class="card">
                <?php if ($img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" 
                         alt="Star Image" 
                         style="width:100%; border-radius:12px;">
                <?php endif; ?>

                <h3 style="margin-top:12px;"><?= htmlspecialchars($title) ?></h3>
                <p><?= htmlspecialchars(mb_strimwidth($desc, 0, 120, "...")) ?></p>
                <p style="font-size: 0.9em; color: var(--muted);"><?= htmlspecialchars($date) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <div class="card">
        <p>No star data available at this time.</p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
