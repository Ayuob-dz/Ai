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

$packages = $conn->query("SELECT * FROM packages WHERE active=1 ORDER BY sort_order");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $package_id = (int)$_POST['package_id'];
    $player_id = clean($_POST['player_id'] ?? '');
    
    if (!validateFreeFireId($player_id)) {
        $error = 'ID فري فاير غير صالح';
    } else {
        $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $pkg = $stmt->get_result()->fetch_assoc();
        
        if (!$pkg) {
            $error = 'الباقة غير موجودة';
        } elseif ($user['wallet_balance'] < $pkg['price']) {
            $error = 'رصيدك غير كافٍ، يرجى شحن المحفظة';
        } else {
            $conn->begin_transaction();
            try {
                $new_balance = $user['wallet_balance'] - $pkg['price'];
                $stmt = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_balance, $user['id']);
                $stmt->execute();
                
                $stmt = $conn->prepare("INSERT INTO orders (user_id, package_id, player_id, amount) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisd", $user['id'], $package_id, $player_id, $pkg['price']);
                $stmt->execute();
                $order_id = $conn->insert_id;
                
                $conn->commit();
                
                if (function_exists('sendTelegramMessage')) {
                    sendTelegramMessage("🛒 طلب جديد رقم $order_id من {$user['username']} - {$pkg['name']}");
                }
                
                $success = "تم إنشاء الطلب بنجاح. سيتم شحن حسابك خلال دقائق.";
                $user = getCurrentUser(); // تحديث الرصيد
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'حدث خطأ، حاول مرة أخرى.';
                log_error($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شراء ألماس</title>
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
                    <a href="buy.php" class="active"><i class="fas fa-shopping-cart"></i> شراء ألماس</a>
                    <a href="orders.php"><i class="fas fa-history"></i> طلباتي</a>
                    <a href="payment-methods.php"><i class="fas fa-credit-card"></i> طرق الدفع</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 content">
                <h2>شراء ألماس</h2>
                <div class="row">
                    <div class="col-md-8">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <?php while ($pkg = $packages->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5><?php echo htmlspecialchars($pkg['name']); ?></h5>
                                        <p><?php echo htmlspecialchars($pkg['description']); ?></p>
                                        <h4 class="text-warning">$<?php echo number_format($pkg['price'], 2); ?></h4>
                                        <button class="btn btn-primary" onclick="showBuyModal(<?php echo $pkg['id']; ?>, '<?php echo htmlspecialchars($pkg['name']); ?>', <?php echo $pkg['price']; ?>)">شراء</button>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>رصيدك الحالي</h5>
                                <h3>$<?php echo number_format($user['wallet_balance'], 2); ?></h3>
                                <a href="deposit.php" class="btn btn-sm btn-primary">شحن المحفظة</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="buyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الشراء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="package_id" id="modalPackageId">
                        <p>الباقة: <strong id="modalPackageName"></strong></p>
                        <p>السعر: <strong id="modalPrice"></strong> $</p>
                        <div class="mb-3">
                            <label>ID فري فاير الخاص بك</label>
                            <input type="text" name="player_id" class="form-control" value="<?php echo htmlspecialchars($user['freefire_id']); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تأكيد الشراء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showBuyModal(id, name, price) {
            document.getElementById('modalPackageId').value = id;
            document.getElementById('modalPackageName').innerText = name;
            document.getElementById('modalPrice').innerText = price;
            new bootstrap.Modal(document.getElementById('buyModal')).show();
        }
    </script>
</body>
</html>
