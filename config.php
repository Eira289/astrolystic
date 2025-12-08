<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// config.php

// 1) Read API key from hosting environment first (Render/Railway)
// 2) Fallback to your local key if you want for MAMP
// 3) Final fallback to DEMO_KEY

$envKey = getenv('NASA_API_KEY');

define('NASA_API_KEY', $envKey ?: 'DEMO_KEY'); 
