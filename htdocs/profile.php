<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/validation.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $username = clean($_POST['username'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $freefire_id = clean($_POST['freefire_id'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new = $_POST['confirm_new'] ?? '';
    
    if (!validateRequired($username)) {
        $error = 'اسم المستخدم مطلوب';
    } elseif (!validateFreeFireId($freefire_id)) {
        $error = 'ID فري فاير غير صالح';
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ?, freefire_id = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $phone, $freefire_id, $user['id']);
        if ($stmt->execute()) {
            $success = 'تم تحديث الملف الشخصي بنجاح.';
            $user = getCurrentUser(); // تحديث البيانات
        } else {
            $error = 'حدث خطأ، حاول مرة أخرى.';
        }
    }
    
    if (empty($error) && !empty($current_password) && !empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $error = 'كلمة المرور الحالية غير صحيحة';
        } elseif (strlen($new_password) < 6) {
            $error = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
        } elseif ($new_password !== $confirm_new) {
            $error = 'كلمتا المرور غير متطابقتين';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user['id']);
            if ($stmt->execute()) {
                $success = 'تم تغيير كلمة المرور بنجاح.';
            } else {
                $error = 'فشل تغيير كلمة المرور.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي</title>
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
                    <a href="profile.php" class="active"><i class="fas fa-user"></i> الملف الشخصي</a>
                    <a href="wallet.php"><i class="fas fa-wallet"></i> محفظتي</a>
                    <a href="buy.php"><i class="fas fa-shopping-cart"></i> شراء ألماس</a>
                    <a href="orders.php"><i class="fas fa-history"></i> طلباتي</a>
                    <a href="payment-methods.php"><i class="fas fa-credit-card"></i> طرق الدفع</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>الملف الشخصي</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h5>تعديل البيانات</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">لا يمكن تغيير البريد الإلكتروني</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ID فري فاير</label>
                                <input type="text" name="freefire_id" class="form-control" value="<?php echo htmlspecialchars($user['freefire_id']); ?>" required>
                            </div>
                            <hr>
                            <h5>تغيير كلمة المرور (اختياري)</h5>
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور الحالية</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور الجديدة</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                                <input type="password" name="confirm_new" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
