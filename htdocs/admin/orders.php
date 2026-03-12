<?php
define('ACCESS_ALLOWED', true);
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

requireAdmin();

$action = $_GET['action'] ?? '';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'view' && $order_id) {
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email, p.name as package_name FROM orders o JOIN users u ON o.user_id = u.id JOIN packages p ON o.package_id = p.id WHERE o.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        die('الطلب غير موجود');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        verifyCSRFToken($_POST['csrf_token'] ?? '');
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $success = 'تم تحديث الحالة';
            $stmt = $conn->prepare("SELECT o.*, u.username, u.email, p.name as package_name FROM orders o JOIN users u ON o.user_id = u.id JOIN packages p ON o.package_id = p.id WHERE o.id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'فشل التحديث';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>تفاصيل الطلب #<?php echo $order_id; ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h2>تفاصيل الطلب #<?php echo $order_id; ?></h2>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <table class="table table-bordered">
                <tr><th>المستخدم</th><td><?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</td></tr>
                <tr><th>الباقة</th><td><?php echo htmlspecialchars($order['package_name']); ?></td></tr>
                <tr><th>ID اللاعب</th><td><?php echo htmlspecialchars($order['player_id']); ?></td></tr>
                <tr><th>المبلغ</th><td>$<?php echo $order['amount']; ?></td></tr>
                <tr><th>الحالة</th><td><?php echo get_status_badge($order['status']); ?></td></tr>
                <tr><th>تاريخ الإنشاء</th><td><?php echo $order['created_at']; ?></td></tr>
                <tr><th>تفاصيل الأتمتة</th><td><?php echo nl2br(htmlspecialchars($order['automation_status'])); ?></td></tr>
            </table>
            <form method="POST">
                <?php csrfField(); ?>
                <div class="mb-3">
                    <label>تحديث الحالة</label>
                    <select name="status" class="form-control">
                        <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>قيد الانتظار</option>
                        <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>قيد التنفيذ</option>
                        <option value="completed" <?php echo $order['status']=='completed'?'selected':''; ?>>مكتمل</option>
                        <option value="failed" <?php echo $order['status']=='failed'?'selected':''; ?>>فاشل</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">تحديث</button>
                <a href="orders.php" class="btn btn-secondary">رجوع</a>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sql = "SELECT o.*, u.username, p.name as package_name FROM orders o JOIN users u ON o.user_id = u.id JOIN packages p ON o.package_id = p.id";
if ($status_filter) {
    $sql .= " WHERE o.status = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY o.created_at DESC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الطلبات</title>
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
                    <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> الطلبات</a>
                    <a href="deposits.php"><i class="fas fa-wallet"></i> طلبات الشحن</a>
                    <a href="users.php"><i class="fas fa-users"></i> المستخدمين</a>
                    <a href="packages.php"><i class="fas fa-cube"></i> الباقات</a>
                    <a href="cards.php"><i class="fas fa-credit-card"></i> بطاقات الفيزا</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>إدارة الطلبات</h2>
                <div class="mb-3">
                    <a href="?status=pending" class="btn btn-sm btn-warning">المعلقة</a>
                    <a href="?status=processing" class="btn btn-sm btn-info">قيد التنفيذ</a>
                    <a href="?status=completed" class="btn btn-sm btn-success">المكتملة</a>
                    <a href="?status=failed" class="btn btn-sm btn-danger">الفاشلة</a>
                    <a href="orders.php" class="btn btn-sm btn-secondary">الكل</a>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>الباقة</th>
                            <th>ID اللاعب</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['player_id']); ?></td>
                            <td>$<?php echo $order['amount']; ?></td>
                            <td><?php echo get_status_badge($order['status']); ?></td>
                            <td><?php echo format_date($order['created_at']); ?></td>
                            <td>
                                <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">عرض</a>
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
