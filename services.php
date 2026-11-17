<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// GET: optional search term from the URL (services.php?q=dental)
$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
$stmt = $conn->prepare('SELECT * FROM services WHERE name LIKE ? OR description LIKE ? ORDER BY name');
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$services = $stmt->get_result();

$pageTitle = 'Services';
include 'includes/header.php';
?>
<div class="container mt-4">
  <h2 class="page-title mb-3">Our services</h2>
  <form class="row g-2 mb-4" method="get" action="services.php">
    <div class="col-8 col-md-5"><input class="form-control" type="search" name="q" placeholder="Search services" value="<?= e($q) ?>" aria-label="Search services"></div>
    <div class="col-4 col-md-2"><button class="btn btn-sb w-100" type="submit">Search</button></div>
  </form>
  <div class="row g-3">
    <?php if ($services->num_rows === 0): ?>
      <div class="col-12"><div class="alert alert-warning">No services match &ldquo;<?= e($q) ?>&rdquo;. Try another word or <a href="services.php" class="alert-link">see all services</a>.</div></div>
    <?php endif; ?>
    <?php while ($s = $services->fetch_assoc()): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card sb-card h-100"><div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= e($s['name']) ?></h5>
          <p class="card-text"><?= e($s['description']) ?></p>
          <p class="text-muted mb-2"><?= (int)$s['duration_min'] ?> minutes</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <span class="price">SCR <?= number_format($s['price'], 2) ?></span>
            <a href="book.php?service=<?= (int)$s['id'] ?>" class="btn btn-sb btn-sm" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Book now</a>
          </div>
        </div></div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
