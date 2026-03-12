<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/validation.php';

requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $username = clean($_POST['username'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $freefire_id = clean($_POST['freefire_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!validateRequired($username)) {
        $error = 'اسم المستخدم مطلوب';
    } elseif (!validateEmail($email)) {
        $error = 'البريد الإلكتروني غير صالح';
    } elseif (!empty($phone) && !validatePhone($phone)) {
        $error = 'رقم الهاتف غير صالح';
    } elseif (!validateFreeFireId($freefire_id)) {
        $error = 'ID فري فاير يجب أن يكون أرقام فقط';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمتا المرور غير متطابقتين';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'البريد الإلكتروني أو اسم المستخدم موجود بالفعل';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, freefire_id, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $phone, $freefire_id, $hashed);
            if ($stmt->execute()) {
                $success = 'تم إنشاء الحساب بنجاح، يمكنك تسجيل الدخول الآن.';
                // إرسال إشعار للأدمن
                if (function_exists('sendTelegramMessage')) {
                    sendTelegramMessage("👤 مستخدم جديد: $username ($email)");
                }
            } else {
                $error = 'حدث خطأ، حاول مرة أخرى.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">إنشاء حساب جديد</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <?php csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف (اختياري)</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ID فري فاير</label>
                                <input type="text" name="freefire_id" class="form-control" required value="<?php echo htmlspecialchars($_POST['freefire_id'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">تسجيل</button>
                        </form>
                        <div class="mt-3 text-center">
                            لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
