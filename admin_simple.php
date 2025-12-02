<?php
// admin_simple.php - Admin dashboard sederhana
session_start();
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: simple_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "kios_bungsu_jaya");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .header { background: #2e7d32; color: white; padding: 20px; display: flex; justify-content: space-between; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; margin: 0 0 10px 0; font-size: 14px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #2e7d32; }
        table { width: 100%; background: white; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .btn { display: inline-block; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn-success { background: #4CAF50; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Admin Dashboard</h1>
        <div>
            <span>Hello, <?php echo $_SESSION["admin_username"]; ?></span>
            <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php
        if (isset($_GET["logout"])) {
            session_destroy();
            header("Location: simple_login.php");
            exit;
        }
        
        // Get stats
        $total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()["total"];
        $total_sales = $conn->query("SELECT SUM(total_price) as total FROM orders")->fetch_assoc()["total"];
        $today = date("Y-m-d");
        $today_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = \"$today\"")->fetch_assoc()["total"];
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
                <h3>Status</h3>
                <div class="value">✅ Active</div>
            </div>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="?refresh=1" class="btn btn-success">🔄 Refresh</a>
            <a href="index.html" class="btn" target="_blank">🌐 Website</a>
        </div>
        
        <h2>Daftar Pesanan</h2>
        <?php
        $result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 20");
        
        if ($result->num_rows == 0) {
            echo "<p>Belum ada pesanan.</p>";
        } else {
            echo "<table>";
            echo "<tr><th>No. Order</th><th>Tanggal</th><th>Customer</th><th>Produk</th><th>Qty</th><th>Total</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["order_number"] . "</td>";
                echo "<td>" . date("d/m/Y", strtotime($row["order_date"])) . "</td>";
                echo "<td>" . $row["customer_name"] . "</td>";
                echo "<td>" . $row["product_name"] . "</td>";
                echo "<td>" . $row["quantity"] . " liter</td>";
                echo "<td>Rp " . number_format($row["total_price"]) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        
        $conn->close();
        ?>
    </div>
</body>
</html>