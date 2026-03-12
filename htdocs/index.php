<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// جلب الباقات النشطة
$packages = $conn->query("SELECT * FROM packages WHERE active=1 AND type='diamond' ORDER BY sort_order");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🔥 <?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="#packages">الباقات</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment-methods.php">طرق الدفع</a></li>
                </ul>
                <div>
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="btn btn-outline-light">لوحة التحكم</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light">تسجيل الدخول</a>
                        <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- قسم الترحيب -->
    <section class="hero bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">اشحن ألماس فري فاير بأفضل الأسعار</h1>
            <p class="lead">شحن فوري وآمن مع هدايا حصرية</p>
            <a href="#packages" class="btn btn-light btn-lg">تصفح الباقات</a>
        </div>
    </section>

    <!-- عرض الباقات -->
    <section id="packages" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">باقات الألماس</h2>
            <div class="row">
                <?php while ($pkg = $packages->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($pkg['description']); ?></p>
                            <h4 class="text-warning">$<?php echo number_format($pkg['price'], 2); ?></h4>
                            <a href="<?php echo isLoggedIn() ? 'buy.php?package=' . $pkg['id'] : 'register.php'; ?>" class="btn btn-primary">اشتر الآن</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- تذييل -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
