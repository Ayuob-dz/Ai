<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/upload.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $amount = floatval($_POST['amount'] ?? 0);
    $transaction_id = clean($_POST['transaction_id'] ?? '');
    
    if ($amount < 1) {
        $error = 'المبلغ يجب أن يكون 1 دولار على الأقل';
    } elseif (empty($transaction_id)) {
        $error = 'رقم العملية مطلوب';
    } elseif (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        $error = 'يرجى اختيار صورة صالحة';
    } else {
        $upload_path = uploadImage($_FILES['screenshot'], 'deposits');
        if (!$upload_path) {
            $error = 'فشل رفع الصورة. تأكد من أنها صورة (jpg,png) وأقل من 5MB.';
        } else {
            $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, transaction_id, screenshot) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $user['id'], $amount, $transaction_id, $upload_path);
            if ($stmt->execute()) {
                if (function_exists('sendTelegramMessage')) {
                    $msg = "💰 طلب إيداع جديد\nالمستخدم: {$user['username']} (ID: {$user['id']})\nالمبلغ: $ {$amount}\nرقم العملية: $transaction_id";
                    sendTelegramMessage($msg);
                }
                $success = 'تم إرسال طلب الإيداع بنجاح، سيتم مراجعته قريباً.';
            } else {
                $error = 'حدث خطأ في حفظ الطلب، حاول مرة أخرى.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شحن المحفظة</title>
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
                    <a href="orders.php"><i class="fas fa-history"></i> طلباتي</a>
                    <a href="payment-methods.php"><i class="fas fa-credit-card"></i> طرق الدفع</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>شحن المحفظة عبر Binance</h2>
                <div class="card p-4">
                    <p>حساب Binance الثابت: <strong>123456789</strong> <button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText('123456789')">نسخ</button></p>
                    <p>يرجى تحويل المبلغ المطلوب إلى هذا الحساب ثم تعبئة النموذج أدناه مع إرفاق لقطة شاشة للعملية.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?php csrfField(); ?>
                        <div class="mb-3">
                            <label>المبلغ ($)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>رقم العملية (Transaction ID)</label>
                            <input type="text" name="transaction_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>لقطة شاشة للعملية</label>
                            <input type="file" name="screenshot" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">إرسال الطلب</button>
                        <a href="wallet.php" class="btn btn-secondary">رجوع</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
