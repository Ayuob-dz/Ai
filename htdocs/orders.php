<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireLogin();

$user = getCurrentUser();

$orders = $conn->query("SELECT o.*, p.name as package_name FROM orders o JOIN packages p ON o.package_id = p.id WHERE o.user_id = {$user['id']} ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلباتي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: #1a1a2e; min-height: 100vh; color: white; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #ff7b00; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h3 class="text-center">🔥 <?php echo SITE_NAME; ?></h3>
                    <hr>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
                    <a href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a>
                    <a href="wallet.php"><i class="fas fa-wallet"></i> محفظتي</a>
                    <a href="buy.php"><i class="fas fa-shopping-cart"></i> شراء ألماس</a>
                    <a href="orders.php" class="active"><i class="fas fa-history"></i> طلباتي</a>
                    <a href="payment-methods.php"><i class="fas fa-credit-card"></i> طرق الدفع</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>طلباتي</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الباقة</th>
                            <th>ID اللاعب</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows == 0): ?>
                            <tr><td colspan="6" class="text-center">لا توجد طلبات سابقة</td></tr>
                        <?php endif; ?>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['player_id']); ?></td>
                            <td>$<?php echo $order['amount']; ?></td>
                            <td><?php echo get_status_badge($order['status']); ?></td>
                            <td><?php echo format_date($order['created_at']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="dashboard.php" class="btn btn-secondary">رجوع</a>
            </div>
        </div>
    </div>
</body>
</html>
