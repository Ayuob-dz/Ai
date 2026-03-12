<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

require_once 'database.php';
require_once 'functions.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateWalletBalance($user_id, $new_balance) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
    return $stmt->execute();
}

function addToWallet($user_id, $amount) {
    global $conn;
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $new_balance = $user['wallet_balance'] + $amount;
    return updateWalletBalance($user_id, $new_balance);
}

function deductFromWallet($user_id, $amount) {
    global $conn;
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user['wallet_balance'] < $amount) return false;
    $new_balance = $user['wallet_balance'] - $amount;
    return updateWalletBalance($user_id, $new_balance);
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        die('غير مصرح لك بالوصول إلى هذه الصفحة');
    }
}
?>
