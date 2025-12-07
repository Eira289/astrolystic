<?php
if (!defined('NASA_API_KEY')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Astrologystic</title>
  <link rel="stylesheet" href="assets/style.css" />
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap" rel="stylesheet">


</head>
<body>
<header>
  <div class="container header-inner">
    <div class="logo">
      <a class="logo-text" href="/astrolystic/index.php" style="text-decoration:none;">
  ASTROLYSTIC
</a>

    </div>
    <nav>
      <a href="index.php">Dashboard</a>
      <a href="astronomy.php">Astronomy</a>
      <a href="stars.php">Stars & Nebulae</a>
      <a href="launches.php">Rocket Launches</a>
    </nav>
  </div>
</header>
<main class="container">
