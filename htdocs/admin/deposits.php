<?php
define('ACCESS_ALLOWED', true);
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

requireAdmin();

// معالجة قبول/رفض الإيداع
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action === 'approve') {
        // قبول الإيداع: تحديث الحالة وإضافة الرصيد
        $stmt = $conn->prepare("SELECT * FROM deposits WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $deposit = $stmt->get_result()->fetch_assoc();
        if ($deposit && $deposit['status'] === 'pending') {
            $conn->begin_transaction();
            $stmt = $conn->prepare("UPDATE deposits SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            addToWallet($deposit['user_id'], $deposit['amount']);
            $conn->commit();
            $success = 'تم قبول الإيداع وإضافة الرصيد';
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE deposits SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = 'تم رفض الإيداع';
    }
}

$deposits = $conn->query("SELECT d.*, u.username FROM deposits d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة طلبات الشحن</title>
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
                    <h3 class="text-center">🔥 الإدارة</h3>
                    <hr>
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a>
                    <a href="deposits.php" class="active"><i class="fas fa-wallet"></i> طلبات الشحن</a>
                    <a href="users.php"><i class="fas fa-users"></i> المستخدمين</a>
                    <a href="packages.php"><i class="fas fa-cube"></i> الباقات</a>
                    <a href="cards.php"><i class="fas fa-credit-card"></i> بطاقات الفيزا</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>إدارة طلبات الشحن</h2>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>المبلغ</th>
                            <th>رقم العملية</th>
                            <th>لقطة الشاشة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($dep = $deposits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $dep['id']; ?></td>
                            <td><?php echo htmlspecialchars($dep['username']); ?></td>
                            <td>$<?php echo $dep['amount']; ?></td>
                            <td><?php echo htmlspecialchars($dep['transaction_id']); ?></td>
                            <td><a href="../<?php echo $dep['screenshot']; ?>" target="_blank">عرض</a></td>
                            <td><?php echo get_status_badge($dep['status']); ?></td>
                            <td><?php echo format_date($dep['created_at']); ?></td>
                            <td>
                                <?php if ($dep['status'] == 'pending'): ?>
                                    <a href="?action=approve&id=<?php echo $dep['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('قبول هذا الإيداع؟')">قبول</a>
                                    <a href="?action=reject&id=<?php echo $dep['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('رفض هذا الإيداع؟')">رفض</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
