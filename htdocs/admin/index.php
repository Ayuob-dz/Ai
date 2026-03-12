<?php
define('ACCESS_ALLOWED', true);
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdmin();

$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$pending_deposits = $conn->query("SELECT COUNT(*) as c FROM deposits WHERE status='pending'")->fetch_assoc()['c'];
$total_income = $conn->query("SELECT SUM(amount) as s FROM orders WHERE status='completed'")->fetch_assoc()['s'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الأدمن</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: #1a1a2e; min-height: 100vh; color: white; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #ff7b00; }
        .content { padding: 20px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h3 class="text-center">🔥 الإدارة</h3>
                    <hr>
                    <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a>
                    <a href="deposits.php"><i class="fas fa-wallet"></i> طلبات الشحن</a>
                    <a href="users.php"><i class="fas fa-users"></i> المستخدمين</a>
                    <a href="packages.php"><i class="fas fa-cube"></i> الباقات</a>
                    <a href="cards.php"><i class="fas fa-credit-card"></i> بطاقات الفيزا</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>لوحة تحكم الأدمن</h2>
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>إجمالي المستخدمين</h5>
                            <h3><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>إجمالي الطلبات</h5>
                            <h3><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>إيداعات معلقة</h5>
                            <h3><?php echo $pending_deposits; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>إجمالي الدخل</h5>
                            <h3>$<?php echo number_format($total_income ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
