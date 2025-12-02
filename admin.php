<?php
// admin.php - Admin Dashboard
session_start();

// Simple login check
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        require_once 'config.php';
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
            }
        }
        
        $stmt->close();
        $conn->close();
        
        if (!isset($_SESSION['admin_logged_in'])) {
            $error = "Login gagal!";
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Admin - Kios Bungsu Jaya</title>
        <style>
            body { font-family: Arial; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 350px; }
            h2 { text-align: center; color: #2e7d32; margin-bottom: 30px; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; color: #555; }
            input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; }
            button { width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
            .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
            .info { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔐 Admin Login</h2>
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required value="admin">
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required value="admin123">
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <div class="info">Default: admin / admin123</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

require_once 'config.php';
$conn = getConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Kios Bungsu Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        
        .header { background: #2e7d32; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .logout { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #2e7d32; }
        
        .actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn { padding: 10px 20px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-success { background: #4CAF50; }
        
        table { width: 100%; background: white; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f9f9f9; }
        
        .status { padding: 5px 10px; border-radius: 3px; font-size: 12px; }
        .pending { background: #fff3cd; color: #856404; }
        .completed { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Admin Dashboard - Kios Bungsu Jaya</h1>
        <a href="?logout=1" class="logout">Logout (<?php echo $_SESSION['admin_username']; ?>)</a>
    </div>
    
    <div class="container">
        <?php
        // Get statistics
        $total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
        $total_sales = $conn->query("SELECT SUM(total_price) as total FROM orders")->fetch_assoc()['total'];
        $today_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = CURDATE()")->fetch_assoc()['total'];
        $total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Penjualan</h3>
                <div class="value">Rp <?php echo number_format($total_sales ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pesanan Hari Ini</h3>
                <div class="value"><?php echo $today_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Produk</h3>
                <div class="value"><?php echo $total_products; ?></div>
            </div>
        </div>
        
        <div class="actions">
            <a href="?refresh=1" class="btn btn-success">🔄 Refresh</a>
            <a href="export_orders.php" class="btn">📥 Export Data</a>
            <a href="index.html" target="_blank" class="btn">🌐 View Website</a>
        </div>
        
        <h2>Daftar Pesanan (<?php echo $total_orders; ?> pesanan)</h2>
        
        <?php if ($total_orders == 0): ?>
            <div style="background: #fff3cd; padding: 30px; border-radius: 10px; text-align: center; margin-top: 20px;">
                <h3 style="color: #856404;">📭 Belum ada pesanan</h3>
                <p>Belum ada data pesanan yang masuk.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 50");
                    while ($order = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['customer_name']); ?><br>
                            <small><?php echo $order['whatsapp']; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo $order['quantity']; ?> liter</td>
                        <td>Rp <?php echo number_format($order['total_price']); ?></td>
                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                        <td>
                            <span class="status <?php echo $order['order_status']; ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top: 40px; background: white; padding: 20px; border-radius: 10px;">
            <h3>📊 Informasi Database</h3>
            <p>Database: kios_bungsu_jaya</p>
            <p>Terakhir diupdate: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><a href="test_connection.php">Test Koneksi Database</a></p>
        </div>
    </div>
    
    <script>
        // Auto refresh every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
<?php $conn->close(); ?>