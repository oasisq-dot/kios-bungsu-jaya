<?php
// super_simple_admin.php - Admin tanpa database
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: super_simple_login.php");
    exit;
}

// Baca data dari file
$orders = [];
if (file_exists("orders_log.txt")) {
    $lines = file("orders_log.txt", FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $orders[] = $line;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kios Bungsu Jaya</title>
    <style>
        body { font-family: Arial; margin: 0; }
        .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .logout { color: white; text-decoration: none; position: absolute; top: 20px; right: 20px; }
        .order-list { background: white; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .order-item { border-bottom: 1px solid #eee; padding: 15px 0; }
        .order-item:last-child { border-bottom: none; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 ADMIN KIOS BUNGSU JAYA</h1>
        <a href="?logout=1" class="logout">Logout</a>
    </div>
    
    <div class="container">
        <?php
        if (isset($_GET["logout"])) {
            session_destroy();
            header("Location: super_simple_login.php");
            exit;
        }
        ?>
        
        <h2>Pesanan Masuk</h2>
        
        <?php if (empty($orders)): ?>
            <div style="background: #fff3cd; padding: 30px; border-radius: 10px; text-align: center;">
                <h3 style="color: #856404;">📭 Belum ada pesanan</h3>
                <p>Coba buat pesanan di website utama.</p>
                <a href="index.html" style="display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px;">
                    Ke Website
                </a>
            </div>
        <?php else: ?>
            <div class="order-list">
                <p>Total Pesanan: <strong><?php echo count($orders); ?></strong></p>
                
                <?php foreach (array_reverse($orders) as $order): ?>
                    <div class="order-item">
                        <pre><?php echo htmlspecialchars($order); ?></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.html" target="_blank" style="background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                🌐 Buka Website
            </a>
        </div>
    </div>
</body>
</html>