<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
}

function require_login() {
    if (!is_logged_in()) {
        flash('warning', 'Please log in to continue.');
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('danger', 'Access denied. This page is for administrators only.');
        header('Location: index.php');
        exit;
    }
}

function status_badge($status) {
    $map = ['Pending' => 'warning text-dark', 'Confirmed' => 'success', 'Completed' => 'secondary', 'Cancelled' => 'danger'];
    return '<span class="badge bg-' . ($map[$status] ?? 'light text-dark') . '">' . e($status) . '</span>';
}
