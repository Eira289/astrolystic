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

<h1 style="margin-bottom: 16px;">Upcoming Rocket Launches</h1>
<p style="color: var(--muted); margin-bottom: 24px;">
  Data from The Space Devs (Launch Library 2). Times may be in UTC.
</p>

<div class="grid grid-3">
  <?php if ($results): ?>
    <?php foreach ($results as $l): ?>
      <div class="card">
        <h2><?= htmlspecialchars($l['name'] ?? 'Unnamed launch') ?></h2>
        <p><strong>NET:</strong> <?= htmlspecialchars($l['net'] ?? 'TBD') ?></p>
        <p><strong>Rocket:</strong> <?= htmlspecialchars($l['rocket']['configuration']['name'] ?? 'Unknown') ?></p>
        <p><?= htmlspecialchars($l['mission']['description'] ?? ($l['launch_service_provider']['name'] ?? '')) ?></p>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No launch data found. Try again later.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
