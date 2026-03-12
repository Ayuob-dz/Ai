<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

// إعدادات قاعدة البيانات - عدل هذه القيم حسب بيانات InfinityFree
define('DB_HOST', 'sql123.infinityfree.com');      // من لوحة التحكم
define('DB_USER', 'if0_12345678');                 // اسم المستخدم
define('DB_PASS', 'your_password');                // كلمة المرور
define('DB_NAME', 'if0_12345678_fireload');        // اسم قاعدة البيانات

// إعدادات الموقع
define('SITE_NAME', 'Fire Load');
define('SITE_URL', 'http://your-site.infinityfreeapp.com'); // غيّر الرابط
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// إعدادات تليجرام (اختياري - اتركها كما هي أو عطل الدالة)
define('TELEGRAM_BOT_TOKEN', '7423907926:AAHdcrw76o6XH54nvGUk1IO7RGQ6j7BCFYY');
define('TELEGRAM_CHAT_ID_ADMIN', '7130722086');

// مفتاح تشفير البطاقات
define('CARD_ENCRYPTION_KEY', 'your-256-bit-secret-key-here-change-it');

// بدء الجلسة
session_start();

// تحديد المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');
?>
