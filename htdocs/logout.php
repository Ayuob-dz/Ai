<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';

// إنهاء الجلسة
$_SESSION = array();
session_destroy();

// حذف الكوكيز
setcookie('user_id', '', time() - 3600, '/');

header('Location: index.php');
exit;
