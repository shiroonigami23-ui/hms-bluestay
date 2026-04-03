<?php
$title = $title ?? 'BlueStay HMS';
$isAuth = Auth::check();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($title) ?></title>
  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/img/app-icon-192.png">
  <link rel="apple-touch-icon" sizes="192x192" href="assets/img/app-icon-192.png">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="app-shell">
  <header class="topbar">
    <a class="brand" href="index.php">
      <img src="assets/img/logo.svg" alt="BlueStay logo">
      <span>BlueStay HMS</span>
    </a>
    <button class="menu-btn" id="menuBtn" aria-label="Toggle menu">☰</button>
    <nav class="main-nav">
      <a href="index.php">Home</a>
      <a href="about.php">About</a>
      <a href="facilities.php">Facilities</a>
      <a href="help.php">Help</a>
      <a href="contact.php">Contact</a>
      <?php if ($isAuth): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a class="btn btn-sm" href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </header>
