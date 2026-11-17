<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_login();   // only logged-in users can book

$slots = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
$services = $conn->query('SELECT * FROM services ORDER BY name')->fetch_all(MYSQLI_ASSOC);

$errors = [];

$old = ['service_id' => (int)($_GET['service'] ?? 0), 'appt_date' => '', 'appt_time' => '', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'service_id' => (int)($_POST['service_id'] ?? 0),
        'appt_date'  => trim($_POST['appt_date'] ?? ''),
        'appt_time'  => trim($_POST['appt_time'] ?? ''),
        'notes'      => trim($_POST['notes'] ?? ''),
    ];

    $stmt = $conn->prepare('SELECT id FROM services WHERE id = ?');
    $stmt->bind_param('i', $old['service_id']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) $errors[] = 'Select a valid service.';
    $stmt->close();

    $d = DateTime::createFromFormat('Y-m-d', $old['appt_date']);
    if (!$d || $d->format('Y-m-d') !== $old['appt_date']) {
        $errors[] = 'Choose a valid date.';
    } elseif ($old['appt_date'] < date('Y-m-d')) {
        $errors[] = 'The date cannot be in the past.';
    } elseif ($d->format('w') === '0') {
        $errors[] = 'We are closed on Sundays. Pick another day.';
    }

    if (!in_array($old['appt_time'], $slots, true)) $errors[] = 'Choose a time slot from the list.';
    if (strlen($old['notes']) > 255) $errors[] = 'Notes must be 255 characters or fewer.';

  
    if (!$errors) {
        $time = $old['appt_time'] . ':00';
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE service_id = ? AND appt_date = ? AND appt_time = ? AND status <> 'Cancelled'");
        $stmt->bind_param('iss', $old['service_id'], $old['appt_date'], $time);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'That slot is already booked. Choose another time.';
        $stmt->close();
    }

   
    if (!$errors) {
        $notes = $old['notes'] === '' ? null : $old['notes'];
        $stmt = $conn->prepare('INSERT INTO appointments (user_id, service_id, appt_date, appt_time, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iisss', $_SESSION['user_id'], $old['service_id'], $old['appt_date'], $time, $notes);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Appointment booked. We will confirm it soon.');
        header('Location: my_appointments.php');
        exit;
    }
}

$pageTitle = 'Book appointment';
include 'includes/header.php';
?>
<div class="container mt-4">
  <div class="card sb-card auth-card"><div class="card-body p-4">
    <h2 class="page-title mb-3">Book an appointment</h2>
    <div id="formErrors" class="alert alert-danger <?= $errors ? '' : 'd-none' ?>">
      <?php if ($errors): ?><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <form id="bookingForm" method="post" action="book.php" onsubmit="return validateBooking()" novalidate>
      <div class="mb-3">
        <label class="form-label" for="service_id">Service</label>
        <select class="form-select" id="service_id" name="service_id" onchange="updateSummary()">
          <option value="">Choose a service</option>
          <?php foreach ($services as $s): ?>
            <option value="<?= (int)$s['id'] ?>" data-price="<?= e($s['price']) ?>" data-duration="<?= (int)$s['duration_min'] ?>" <?= $old['service_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="appt_date">Date</label><input class="form-control" type="date" id="appt_date" name="appt_date" min="<?= date('Y-m-d') ?>" value="<?= e($old['appt_date']) ?>"></div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="appt_time">Time</label>
          <select class="form-select" id="appt_time" name="appt_time" onchange="updateSummary()">
            <option value="">Choose a time</option>
            <?php foreach ($slots as $t): ?><option value="<?= $t ?>" <?= $old['appt_time'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="mb-3"><label class="form-label" for="notes">Notes (optional)</label><textarea class="form-control" id="notes" name="notes" rows="2" maxlength="255"><?= e($old['notes']) ?></textarea></div>
      <div class="alert alert-secondary" id="summaryBox">Choose a service to see the price and duration.</div>
      <button class="btn btn-sb w-100" type="submit" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Book appointment</button>
    </form>
  </div></div>
</div>
<?php include 'includes/footer.php'; ?>
