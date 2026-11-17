<?php
require_once 'includes/auth.php';
// Logout: remove all session data, then start a clean session for the message
$_SESSION = [];
session_destroy();
session_start();
flash('info', 'You have been logged out.');
header('Location: login.php');
exit;
