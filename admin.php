<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_admin();   // Admin-only page

$actions = ['confirm' => 'Confirmed', 'complete' => 'Completed', 'cancel' => 'Cancelled'];
if (isset($_GET['action'], $_GET['id']) && isset($actions[$_GET['action']])) {
    $newStatus = $actions[$_GET['action']];
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $newStatus, $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'Appointment marked as ' . $newStatus . '.');
    header('Location: admin.php');
    exit;
}

$svcErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $dur   = $_POST['duration_min'] ?? '';
    if ($name === '') $svcErrors[] = 'Service name is required.';
    if ($desc === '') $svcErrors[] = 'Description is required.';
    if (!is_numeric($price) || $price <= 0) $svcErrors[] = 'Enter a price greater than 0.';
    if (!ctype_digit((string)$dur) || (int)$dur < 5) $svcErrors[] = 'Duration must be at least 5 minutes.';
    if (!$svcErrors) {
        $price = (float)$price; $dur = (int)$dur;
        $stmt = $conn->prepare('INSERT INTO services (name, description, price, duration_min) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssdi', $name, $desc, $price, $dur);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Service added.');
        header('Location: admin.php');
        exit;
    }
}

$total = (int)$conn->query('SELECT COUNT(*) AS c FROM appointments')->fetch_assoc()['c'];
$byStatus = ['Pending' => 0, 'Confirmed' => 0, 'Completed' => 0, 'Cancelled' => 0];
foreach ($conn->query('SELECT status, COUNT(*) AS c FROM appointments GROUP BY status') as $r) $byStatus[$r['status']] = (int)$r['c'];
$userCount = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$barColor = ['Pending' => 'warning', 'Confirmed' => 'success', 'Completed' => 'secondary', 'Cancelled' => 'danger'];

$filter = $_GET['status'] ?? '';
$sql = 'SELECT a.id, a.appt_date, a.appt_time, a.status, u.full_name, s.name FROM appointments a JOIN users u ON a.user_id = u.id JOIN services s ON a.service_id = s.id';
if (isset($byStatus[$filter])) {
    $stmt = $conn->prepare($sql . ' WHERE a.status = ? ORDER BY a.appt_date, a.appt_time');
    $stmt->bind_param('s', $filter);
} else {
    $filter = '';
    $stmt = $conn->prepare($sql . ' ORDER BY a.appt_date, a.appt_time');
}
$stmt->execute();
$rows = $stmt->get_result();

$pageTitle = 'Admin dashboard';
include 'includes/header.php';
?>
<div class="container mt-4">
  <h2 class="page-title mb-3">Admin dashboard</h2>

  <div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card sb-card p-3 text-center"><div class="stat-number"><?= $userCount ?></div>Registered patients</div></div>
    <div class="col-md-8"><div class="card sb-card p-3">
      <strong class="mb-2">Appointments by status (<?= $total ?> total)</strong>
      <?php foreach ($byStatus as $st => $n): $p = $total ? round($n / $total * 100) : 0; ?>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span style="width:90px"><?= e($st) ?></span>
          <div class="progress flex-grow-1" role="progressbar" aria-label="<?= e($st) ?>" aria-valuenow="<?= $p ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-<?= $barColor[$st] ?>" style="width: <?= $p ?>%"><?= $n ?></div></div>
        </div>
      <?php endforeach; ?>
    </div></div>
  </div>

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
    <h4 class="mb-0">All appointments</h4>
    <div class="btn-group btn-group-sm">
      <a class="btn btn-outline-secondary <?= $filter === '' ? 'active' : '' ?>" href="admin.php">All</a>
      <?php foreach (array_keys($byStatus) as $st): ?><a class="btn btn-outline-secondary <?= $filter === $st ? 'active' : '' ?>" href="admin.php?status=<?= e($st) ?>"><?= e($st) ?></a><?php endforeach; ?>
    </div>
  </div>
  <div class="table-responsive card sb-card mb-4">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Patient</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($rows->num_rows === 0): ?><tr><td colspan="6" class="text-center text-muted py-3">No appointments found.</td></tr><?php endif; ?>
      <?php while ($r = $rows->fetch_assoc()): ?>
        <tr>
          <td><?= e($r['full_name']) ?></td><td><?= e($r['name']) ?></td>
          <td><?= e(date('d M Y', strtotime($r['appt_date']))) ?></td><td><?= e(substr($r['appt_time'], 0, 5)) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td class="text-nowrap">
            <?php if ($r['status'] === 'Pending'): ?><a class="btn btn-success btn-sm" href="admin.php?action=confirm&id=<?= (int)$r['id'] ?>">Confirm</a><?php endif; ?>
            <?php if ($r['status'] === 'Confirmed'): ?><a class="btn btn-secondary btn-sm" href="admin.php?action=complete&id=<?= (int)$r['id'] ?>">Complete</a><?php endif; ?>
            <?php if (in_array($r['status'], ['Pending', 'Confirmed'])): ?><a class="btn btn-outline-danger btn-sm" href="admin.php?action=cancel&id=<?= (int)$r['id'] ?>">Cancel</a><?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="card sb-card"><div class="card-body">
    <h4 class="mb-3">Add a service</h4>
    <?php if ($svcErrors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($svcErrors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="admin.php" class="row g-2">
      <input type="hidden" name="add_service" value="1">
      <div class="col-md-3"><input class="form-control" name="name" placeholder="Service name" aria-label="Service name"></div>
      <div class="col-md-4"><input class="form-control" name="description" placeholder="Short description" aria-label="Description"></div>
      <div class="col-6 col-md-2"><input class="form-control" type="number" step="0.01" name="price" placeholder="Price SCR" aria-label="Price"></div>
      <div class="col-6 col-md-2"><input class="form-control" type="number" name="duration_min" placeholder="Minutes" aria-label="Duration in minutes"></div>
      <div class="col-md-1"><button class="btn btn-sb w-100" type="submit">Add</button></div>
    </form>
  </div></div>
</div>
<?php include 'includes/footer.php'; ?>
