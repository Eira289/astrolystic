<?php
require_once __DIR__ . '/config.php';

function fetch_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// -----------------------------
// Launches WITH CACHING (Render-safe)
// -----------------------------
$cache_file = rtrim(sys_get_temp_dir(), '/\\') . '/cache_launches.json';
$cache_lifetime = 300;

$launches_json = null;

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_lifetime)) {
    $launches_json = file_get_contents($cache_file);
} else {
    $launches_json = fetch_api("https://ll.thespacedevs.com/2.2.0/launch/upcoming/?limit=12");
    if ($launches_json && strlen($launches_json) > 10) {
        @file_put_contents($cache_file, $launches_json);
    }
}

$launches = $launches_json ? json_decode($launches_json, true) : [];
$results = $launches['results'] ?? [];

include __DIR__ . '/partials/header.php';
?>

<h1 style="margin-bottom: 16px;">Upcoming Rocket Launches</h1>
<p style="color: var(--muted); margin-bottom: 24px;">
  Data from The Space Devs (Launch Library 2). Times may be in UTC.
</p>

<div class="grid grid-3">
  <?php if (!empty($results)): ?>
    <?php foreach ($results as $l): ?>
      <div class="card">
        <h2><?= htmlspecialchars($l['name'] ?? 'Unnamed launch') ?></h2>
        <p><strong>NET:</strong> <?= htmlspecialchars($l['net'] ?? 'TBD') ?></p>
        <p><strong>Provider:</strong> <?= htmlspecialchars($l['launch_service_provider']['name'] ?? 'Unknown') ?></p>
        <p><strong>Rocket:</strong> <?= htmlspecialchars($l['rocket']['configuration']['name'] ?? 'Unknown') ?></p>
        <p style="color: var(--muted);">
          <?= htmlspecialchars($l['mission']['description'] ?? '') ?>
        </p>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="card">
      <p>No launch data found. Try again later.</p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
