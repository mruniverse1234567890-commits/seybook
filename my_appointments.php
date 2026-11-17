<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_login();

// GET: ?cancel=ID cancels one of the user's own appointments
if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status IN ('Pending','Confirmed')");
    $stmt->bind_param('ii', $id, $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->affected_rows > 0) flash('success', 'Appointment cancelled.');
    else flash('danger', 'That appointment could not be cancelled.');
    $stmt->close();
    header('Location: my_appointments.php');
    exit;
}

// Retrieve this user's appointments (JOIN with services)
$stmt = $conn->prepare('SELECT a.id, a.appt_date, a.appt_time, a.notes, a.status, s.name, s.price FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.user_id = ? ORDER BY a.appt_date DESC, a.appt_time DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result();

$pageTitle = 'My appointments';
include 'includes/header.php';
?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">My appointments</h2>
    <a href="book.php" class="btn btn-sb" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Book another</a>
  </div>
  <?php if ($rows->num_rows === 0): ?>
    <div class="alert alert-info">You have no appointments yet. <a href="book.php" class="alert-link">Book your first one.</a></div>
  <?php else: ?>
  <div class="table-responsive card sb-card">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Service</th><th>Date</th><th>Time</th><th>Price (SCR)</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php while ($r = $rows->fetch_assoc()): ?>
        <tr>
          <td><?= e($r['name']) ?><?php if ($r['notes']): ?><div class="small text-muted"><?= e($r['notes']) ?></div><?php endif; ?></td>
          <td><?= e(date('D, d M Y', strtotime($r['appt_date']))) ?></td>
          <td><?= e(substr($r['appt_time'], 0, 5)) ?></td>
          <td><?= number_format($r['price'], 2) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td class="text-end"><?php if (in_array($r['status'], ['Pending', 'Confirmed'])): ?><button class="btn btn-outline-danger btn-sm" onclick="openCancel(<?= (int)$r['id'] ?>)">Cancel</button><?php endif; ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Modal: confirm cancellation -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="cancelTitle">Cancel this appointment?</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body">The slot will be released for other patients. This cannot be undone.</div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep appointment</button><a id="confirmCancel" href="#" class="btn btn-danger">Cancel appointment</a></div>
  </div></div>
</div>
<?php include 'includes/footer.php'; ?>
