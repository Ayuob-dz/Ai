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
    
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (!validateEmail($email)) {
        $error = 'البريد الإلكتروني غير صالح';
    } elseif (empty($password)) {
        $error = 'كلمة المرور مطلوبة';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                
                if ($remember) {
                    // يمكن إضافة كود تذكرني متقدم باستخدام token
                    setcookie('user_id', $user['id'], time() + (86400 * 30), '/');
                }
                
                $redirect = $_SESSION['redirect_to'] ?? 'dashboard.php';
                unset($_SESSION['redirect_to']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'البريد الإلكتروني غير مسجل';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e, #16213e); display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: white; border-radius: 20px; padding: 40px; width: 100%; max-width: 400px; }
        .logo { text-align: center; font-size: 2rem; font-weight: bold; color: #ff7b00; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">🔥 <?php echo SITE_NAME; ?></div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <?php csrfField(); ?>
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">تذكرني</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
        </form>
        <div class="mt-3 text-center">
            ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
        </div>
    </div>
</body>
</html>
