<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

function log_error($message) {
    global $conn;
    $message = $conn->real_escape_string($message);
    $conn->query("INSERT INTO system_logs (log_type, message) VALUES ('error', '$message')");
}

function format_date($datetime) {
    return date('Y-m-d H:i', strtotime($datetime));
}

function format_amount($amount) {
    return number_format($amount, 2) . ' $';
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">قيد الانتظار</span>',
        'processing' => '<span class="badge bg-info">قيد التنفيذ</span>',
        'completed' => '<span class="badge bg-success">مكتمل</span>',
        'failed' => '<span class="badge bg-danger">فاشل</span>',
        'approved' => '<span class="badge bg-success">مقبول</span>',
        'rejected' => '<span class="badge bg-danger">مرفوض</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">غير معروف</span>';
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>
