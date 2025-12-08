<?php
require_once __DIR__ . '/config.php';

function fetch_api(string $url): ?string {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // OK for local MAMP
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result ?: null;
}

$today = date("Y-m-d");
$date  = $_GET['date'] ?? $today;

// Clamp date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = $today;
if ($date > $today) $date = $today;
$minDate = "1995-06-16";
if ($date < $minDate) $date = $minDate;

// Prev/Next
$prev = date("Y-m-d", strtotime("$date -1 day"));
$next = date("Y-m-d", strtotime("$date +1 day"));

// Fetch APOD for selected date
$apod_json = fetch_api("https://api.nasa.gov/planetary/apod?date={$date}&api_key=" . NASA_API_KEY);
$apod = $apod_json ? json_decode($apod_json, true) : null;

?>
<?php include __DIR__ . '/partials/header.php'; ?>

<h1 style="margin-bottom: 10px;">Astronomy Picture of the Day</h1>

<!-- NICE UX DATE PICKER (clickable whole box + auto-submit) -->
<form method="get" action="astronomy.php" style="margin: 0 0 18px;">
  <label style="display:block; color:var(--muted); margin-bottom:10px; font-size:.95rem;">
    Choose a date:
  </label>

  <div
    id="apodDateWrap"
    onclick="openApodPicker()"
    style="
      width:100%;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 12px 14px;
      cursor: pointer;
      user-select: none;
    "
    title="Click to pick a date"
  >
    <input
      id="apodDate"
      type="date"
      name="date"
      value="<?= htmlspecialchars($date) ?>"
      min="<?= htmlspecialchars($minDate) ?>"
      max="<?= htmlspecialchars($today) ?>"
      style="
        flex:1;
        background: transparent;
        border: none;
        color: var(--text);
        font-size: 1rem;
        outline: none;
        cursor: pointer;
      "
    />
    <span style="color:var(--muted); font-size: 0.95rem;"></span>
  </div>

  <noscript>
    <button type="submit" style="margin-top:12px;">View</button>
  </noscript>
</form>

<div class="card">
  <?php if ($apod && !empty($apod['url'])): ?>

    <h2 style="margin-top:0;"><?= htmlspecialchars($apod['title'] ?? '') ?></h2>

    <?php if (($apod['media_type'] ?? '') === 'image'): ?>
      <img src="<?= htmlspecialchars($apod['url']) ?>" alt="APOD image">
    <?php else: ?>
      <p>
        This APOD is a <strong><?= htmlspecialchars($apod['media_type'] ?? 'media') ?></strong>.
        <a href="<?= htmlspecialchars($apod['url']) ?>" target="_blank" style="color:var(--accent);">Open it here</a>.
      </p>
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($apod['explanation'] ?? '')) ?></p>

    <p style="opacity:.75; font-size:.92rem; margin-top:14px;">
      <strong>Date:</strong> <?= htmlspecialchars($apod['date'] ?? $date) ?>
      <?php if (!empty($apod['copyright'])): ?>
        &nbsp;•&nbsp;<strong>Credit:</strong> <?= htmlspecialchars($apod['copyright']) ?>
      <?php endif; ?>
    </p>

    <!-- Prev/Next -->
    <div style="margin-top:18px; display:flex; gap:14px; flex-wrap:wrap;">
      <a href="astronomy.php?date=<?= htmlspecialchars($prev) ?>" style="color:var(--accent); text-decoration:none;">
        ← Previous Day
      </a>

      <?php if ($date !== $today): ?>
        <a href="astronomy.php?date=<?= htmlspecialchars($today) ?>" style="color:var(--accent); text-decoration:none;">
          Today
        </a>
      <?php endif; ?>

      <?php if ($date < $today): ?>
        <a href="astronomy.php?date=<?= htmlspecialchars($next) ?>" style="color:var(--accent); text-decoration:none;">
          Next Day →
        </a>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <p><strong>APOD not available for this date.</strong></p>
    <p style="color:var(--muted);">
      Try a different day (sometimes NASA has missing entries).
    </p>
  <?php endif; ?>
</div>

<script>
function openApodPicker(){
  const el = document.getElementById('apodDate');
  if (!el) return;
  // Open native picker if supported; otherwise focus/click
  if (el.showPicker) el.showPicker();
  else { el.focus(); el.click(); }
}

// Auto-submit immediately after choosing a date (nice UX)
document.getElementById('apodDate')?.addEventListener('change', function () {
  // If user selects a date, load it right away
  this.form.submit();
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
