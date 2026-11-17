<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Statistics shown on the home page
$userCount  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$apptCount  = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()['c'];
$todayCount = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE appt_date = CURDATE() AND status <> 'Cancelled'")->fetch_assoc()['c'];
$capacity   = 20; // appointments the clinic can handle per day
$pct        = min(100, round($todayCount / $capacity * 100));
$featured   = $conn->query("SELECT * FROM services ORDER BY id LIMIT 3");

$pageTitle = 'Home';
include 'includes/header.php';
?>
<!-- Image carousel -->
<div id="heroCarousel" class="carousel slide sb-slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="assets/img/slide1.svg" alt="Sunrise over a Seychelles lagoon with granite boulders">
      <div class="carousel-caption"><h2>Book your visit in a minute</h2><p>Pick a service, a date and a time. No phone calls, no queues.</p></div>
    </div>
    <div class="carousel-item">
      <img src="assets/img/slide2.svg" alt="Calm turquoise water">
      <div class="carousel-caption"><h2>Care for the whole family</h2><p>Doctors, dentists, physiotherapists and more in one place.</p></div>
    </div>
    <div class="carousel-item">
      <img src="assets/img/slide3.svg" alt="Sunset over the sea">
      <div class="carousel-caption"><h2>Manage it all online</h2><p>See, confirm and cancel appointments from your phone.</p></div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
</div>

<div class="container mt-4">
  <?php if (is_logged_in()): ?>
    <!-- Text is replaced by the greetUser() JavaScript function -->
    <div class="alert alert-info" id="welcomeMsg" data-name="<?= e($_SESSION['full_name']) ?>">Welcome, <?= e($_SESSION['full_name']) ?>!</div>
  <?php else: ?>
    <div class="alert alert-warning">New here? <a href="register.php" class="alert-link">Create a free account</a> to book appointments.</div>
  <?php endif; ?>

  <div class="row g-3 text-center my-3">
    <div class="col-6 col-md-4"><div class="card sb-card p-3"><div class="stat-number count" data-target="<?= (int)$userCount ?>">0</div><div>Registered patients</div></div></div>
    <div class="col-6 col-md-4"><div class="card sb-card p-3"><div class="stat-number count" data-target="<?= (int)$apptCount ?>">0</div><div>Appointments booked</div></div></div>
    <div class="col-12 col-md-4">
      <div class="card sb-card p-3 h-100 justify-content-center">
        <div class="mb-2">Today: <strong><?= (int)$todayCount ?></strong> of <?= $capacity ?> slots taken</div>
        <div class="progress" role="progressbar" aria-label="Slots taken today" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar bg-success" style="width: <?= $pct ?>%"><?= $pct ?>%</div>
        </div>
      </div>
    </div>
  </div>

  <h2 class="page-title mt-4 mb-3">Popular services</h2>
  <div class="row g-3">
    <?php while ($s = $featured->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="card sb-card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= e($s['name']) ?></h5>
            <p class="card-text"><?= e($s['description']) ?></p>
            <div class="mt-auto d-flex justify-content-between align-items-center">
              <span class="price">SCR <?= number_format($s['price'], 2) ?></span>
              <a href="book.php?service=<?= (int)$s['id'] ?>" class="btn btn-sb btn-sm" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Book now</a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <div class="d-flex flex-wrap gap-2 mt-4 align-items-center">
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#hoursModal">Opening hours</button>
    <button class="btn btn-outline-secondary" onclick="showTip()">Show a booking tip</button>
    <span id="tipBox" class="text-muted"></span>
  </div>
</div>

<!-- Modal: opening hours -->
<div class="modal fade" id="hoursModal" tabindex="-1" aria-labelledby="hoursTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="hoursTitle">Opening hours</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <table class="table table-sm mb-0"><tbody>
          <tr><td>Monday to Friday</td><td>08:00 &ndash; 17:00</td></tr>
          <tr><td>Saturday</td><td>08:00 &ndash; 12:00</td></tr>
          <tr><td>Sunday and public holidays</td><td>Closed</td></tr>
        </tbody></table>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
