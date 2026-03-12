<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireLogin();

$user = getCurrentUser();

$deposits = $conn->query("SELECT * FROM deposits WHERE user_id = {$user['id']} ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محفظتي</title>
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
                    <a href="wallet.php" class="active"><i class="fas fa-wallet"></i> محفظتي</a>
                    <a href="buy.php"><i class="fas fa-shopping-cart"></i> شراء ألماس</a>
                    <a href="orders.php"><i class="fas fa-history"></i> طلباتي</a>
                    <a href="payment-methods.php"><i class="fas fa-credit-card"></i> طرق الدفع</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>محفظتي</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>الرصيد الحالي: <span class="text-success">$<?php echo number_format($user['wallet_balance'], 2); ?></span></h4>
                        <a href="deposit.php" class="btn btn-primary">شحن المحفظة</a>
                    </div>
                </div>
                
                <h3>سجل الإيداعات</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المبلغ</th>
                            <th>رقم العملية</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($deposits->num_rows == 0): ?>
                            <tr><td colspan="5" class="text-center">لا توجد إيداعات سابقة</td></tr>
                        <?php endif; ?>
                        <?php while ($dep = $deposits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $dep['id']; ?></td>
                            <td>$<?php echo $dep['amount']; ?></td>
                            <td><?php echo htmlspecialchars($dep['transaction_id']); ?></td>
                            <td><?php echo get_status_badge($dep['status']); ?></td>
                            <td><?php echo format_date($dep['created_at']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
