<?php
require_once __DIR__ . '/auth.php';
$current = basename($_SERVER['PHP_SELF']);
function nav_active($file) {
    global $current;
    return $current === $file ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'SeyBook') ?> | SeyBook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sb-nav sticky-top">
  <div class="container">
    <a class="navbar-brand sb-brand" href="index.php">SeyBook</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link<?= nav_active('index.php') ?>" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('services.php') ?>" href="services.php">Services</a></li>
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link<?= nav_active('book.php') ?>" href="book.php">Book appointment</a></li>
          <li class="nav-item"><a class="nav-link<?= nav_active('my_appointments.php') ?>" href="my_appointments.php">My appointments</a></li>
        <?php endif; ?>
        <?php if (is_admin()): ?>
          <li class="nav-item"><a class="nav-link<?= nav_active('admin.php') ?>" href="admin.php">Admin dashboard</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <?php if (is_logged_in()): ?>
          <span class="navbar-text text-white-50 me-2"><?= e($_SESSION['username']) ?> (<?= e($_SESSION['role']) ?>)</span>
          <a class="btn btn-outline-light btn-sm" href="logout.php">Log out</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm" href="login.php">Log in</a>
          <a class="btn btn-warning btn-sm" href="register.php">Create account</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main>
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <?php foreach ($_SESSION['flash'] as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endforeach; unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>
