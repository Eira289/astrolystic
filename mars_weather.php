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

/* -----------------------------------------------------
   FETCH MARS WEATHER (InSight API)
------------------------------------------------------*/
$weather_json = fetch_api("https://api.nasa.gov/insight_weather/?api_key=" . NASA_API_KEY . "&feedtype=json&ver=1.0");
$weather = $weather_json ? json_decode($weather_json, true) : null;

$sol_keys = $weather['sol_keys'] ?? [];
$latest_sol = end($sol_keys);
$latest_data = $latest_sol ? ($weather[$latest_sol] ?? null) : null;

/* -----------------------------------------------------
   FETCH RANDOM MARS ROVER PHOTO (Option B - guaranteed)
------------------------------------------------------*/

// 1. Get rover manifest
$manifest_json = fetch_api("https://api.nasa.gov/mars-photos/api/v1/manifests/curiosity?api_key=" . NASA_API_KEY);
$manifest = $manifest_json ? json_decode($manifest_json, true) : null;

$mars_img_url = null;

if (!empty($manifest['photo_manifest']['photos'])) {
    $photos = $manifest['photo_manifest']['photos'];

    // 2. Choose a random sol that has photos
    $random_day = $photos[array_rand($photos)];
    $random_sol = $random_day['sol'];

    // 3. Fetch the images for that sol
    $photo_json = fetch_api("https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?sol={$random_sol}&api_key=" . NASA_API_KEY);
    $photo_data = $photo_json ? json_decode($photo_json, true) : null;

    // 4. Pick the first available image
    $mars_img_url = $photo_data['photos'][0]['img_src'] ?? null;
}

include __DIR__ . '/partials/header.php';
?>

<h1>Mars Weather</h1>
<p style="color: var(--muted); margin-bottom: 20px;">
    Real atmospheric data from NASA’s InSight lander, combined with rover imagery from Curiosity.
</p>

<!-- MAIN FEATURED IMAGE -->
<?php if ($mars_img_url): ?>
    <div class="card" style="margin-bottom: 24px;">
        
        <img src="<?= htmlspecialchars($mars_img_url) ?>"
             alt="Mars Rover Photo"
             style="width:100%; border-radius:16px;">
    </div>
<?php endif; ?>

<?php if ($latest_data): ?>

    <?php
    $high = $latest_data['AT']['mx'] ?? null;
    $low  = $latest_data['AT']['mn'] ?? null;
    $earth_date = $latest_data['First_UTC'] ?? null;
    $earth_date = $earth_date ? date("F j, Y", strtotime($earth_date)) : "";
    ?>

    <!-- HERO WEATHER CARD -->
    <div class="card" style="padding: 28px; margin-bottom: 30px; background: linear-gradient(135deg, #5c1f1f, #2e0c0c);">
        <h2 style="font-size: 1.8rem; margin-bottom: 8px;">
            Sol <?= htmlspecialchars($latest_sol) ?> — <?= htmlspecialchars($earth_date) ?>
        </h2>

        <?php if ($high !== null && $low !== null): ?>
            <p style="font-size: 1.2rem;">
                <strong>High:</strong> <?= round($high) ?>°C &nbsp;&nbsp;&nbsp;
                <strong>Low:</strong> <?= round($low) ?>°C
            </p>
        <?php else: ?>
            <p>No temperature data available.</p>
        <?php endif; ?>

        <?php if (!empty($latest_data['PRE'])): ?>
            <p><strong>Pressure:</strong> <?= round($latest_data['PRE']['av']) ?> Pa</p>
        <?php endif; ?>

        <?php if (!empty($latest_data['HWS'])): ?>
            <p><strong>Wind Speed:</strong> <?= round($latest_data['HWS']['av']) ?> m/s</p>
        <?php endif; ?>
    </div>

    <!-- FORECAST GRID -->
    <div class="grid grid-3">
        <?php foreach (array_reverse($sol_keys) as $sol): ?>
            <?php if (!isset($weather[$sol]['AT'])) continue; ?>

            <?php
            $wd = $weather[$sol];
            $h = $wd['AT']['mx'];
            $l = $wd['AT']['mn'];
            $date = $wd['First_UTC'] ? date("M j", strtotime($wd['First_UTC'])) : "";
            ?>
            <div class="card">
                <h3 style="margin-bottom: 4px;">Sol <?= $sol ?></h3>
                <p style="color: var(--muted); margin-bottom: 8px;"><?= $date ?></p>
                <p><strong>High:</strong> <?= round($h) ?>°C</p>
                <p><strong>Low:</strong> <?= round($l) ?>°C</p>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <div class="card">
        <p>No Mars weather data available at this time.</p>
    </div>

<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
