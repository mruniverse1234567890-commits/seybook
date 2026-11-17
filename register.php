<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (is_logged_in()) { header('Location: index.php'); exit; }

$errors = [];
$old = ['full_name' => '', 'email' => '', 'username' => ''];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user  = trim($_POST['username'] ?? '');
    $pw    = $_POST['password'] ?? '';
    $pw2   = $_POST['confirm_password'] ?? '';
    $old   = ['full_name' => $full, 'email' => $email, 'username' => $user];


    if ($full === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($user) < 4) $errors[] = 'Username must be at least 4 characters.';
    if (strlen($pw) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($pw !== $pw2) $errors[] = 'Passwords do not match.';

  
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $user, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'That username or email is already registered.';
        $stmt->close();
    }

   
    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param('ssss', $full, $email, $user, $hash);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Account created. You can log in now.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Create account';
include 'includes/header.php';
?>
<div class="container mt-4">
  <div class="card sb-card auth-card"><div class="card-body p-4">
    <h2 class="page-title mb-3">Create your account</h2>
    <div id="formErrors" class="alert alert-danger <?= $errors ? '' : 'd-none' ?>">
      <?php if ($errors): ?><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <form id="registerForm" method="post" action="register.php" onsubmit="return validateRegister()" novalidate>
      <div class="mb-3"><label class="form-label" for="full_name">Full name</label><input class="form-control" type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>"></div>
      <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e($old['email']) ?>"></div>
      <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control" type="text" id="username" name="username" value="<?= e($old['username']) ?>"></div>
      <div class="mb-3"><label class="form-label" for="password">Password (8+ characters)</label><input class="form-control" type="password" id="password" name="password"></div>
      <div class="mb-3"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" type="password" id="confirm_password" name="confirm_password"></div>
      <button class="btn btn-sb w-100" type="submit" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Create account</button>
    </form>
    <p class="mt-3 mb-0">Already registered? <a href="login.php">Log in</a></p>
  </div></div>
</div>
<?php include 'includes/footer.php'; ?>
