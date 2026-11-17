<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (is_logged_in()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pw   = $_POST['password'] ?? '';

    // Validate credentials against the database
    $stmt = $conn->prepare('SELECT id, full_name, username, password, role FROM users WHERE username = ?');
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($pw, $row['password'])) {
        session_regenerate_id(true);               // new session id after login
        $_SESSION['user_id']   = $row['id'];       // session management
        $_SESSION['username']  = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['role']      = $row['role'];
        flash('success', 'Login successful.');
        header('Location: ' . ($row['role'] === 'admin' ? 'admin.php' : 'index.php'));
        exit;
    }
    $error = 'Wrong username or password. Try again.';
}

$pageTitle = 'Log in';
include 'includes/header.php';
?>
<div class="container mt-4">
  <div class="card sb-card auth-card"><div class="card-body p-4">
    <h2 class="page-title mb-3">Log in</h2>
    <div id="formErrors" class="alert alert-danger <?= $error ? '' : 'd-none' ?>"><?= e($error) ?></div>
    <form id="loginForm" method="post" action="login.php" onsubmit="return validateLogin()" novalidate>
      <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control" type="text" id="username" name="username"></div>
      <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password"></div>
      <button class="btn btn-sb w-100" type="submit" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">Log in</button>
    </form>
    <p class="mt-3 mb-0">No account yet? <a href="register.php">Create one</a></p>
  </div></div>
</div>
<?php include 'includes/footer.php'; ?>
