<?php
// fix_login.php - Fix admin login issues
echo "<h2>🔧 FIX LOGIN ADMIN</h2>";
echo "<div style='font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto;'>";

// Konfigurasi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kios_bungsu_jaya";

// 1. Cek koneksi MySQL
echo "<h3>1. Testing MySQL Connection...</h3>";
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("❌ MySQL Error: " . $conn->connect_error . "<br>Periksa apakah MySQL di XAMPP running.");
}

echo "✅ MySQL Connected<br>";

// 2. Cek database
echo "<h3>2. Checking Database...</h3>";
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");

if ($result->num_rows == 0) {
    echo "❌ Database '$dbname' not found.<br>";
    echo "Creating database...<br>";
    
    // Buat database
    $sql = "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Database created<br>";
    } else {
        die("❌ Failed to create database: " . $conn->error);
    }
} else {
    echo "✅ Database found<br>";
}

// 3. Pilih database
$conn->select_db($dbname);

// 4. Cek tabel admins
echo "<h3>3. Checking admins table...</h3>";
$result = $conn->query("SHOW TABLES LIKE 'admins'");

if ($result->num_rows == 0) {
    echo "❌ Table 'admins' not found<br>";
    echo "Creating table...<br>";
    
    // Buat tabel admins
    $sql = "CREATE TABLE admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table 'admins' created<br>";
    } else {
        die("❌ Failed to create table: " . $conn->error);
    }
} else {
    echo "✅ Table 'admins' found<br>";
}

// 5. Cek data admin
echo "<h3>4. Checking admin data...</h3>";
$result = $conn->query("SELECT * FROM admins WHERE username = 'admin'");

if ($result->num_rows == 0) {
    echo "❌ Admin user not found<br>";
    echo "Creating admin user...<br>";
    
    // Buat password SIMPLE (tanpa hash untuk testing)
    $simple_password = 'admin123';
    $hashed_password = password_hash($simple_password, PASSWORD_DEFAULT);
    
    // Insert admin
    $sql = "INSERT INTO admins (username, password, full_name, email) 
            VALUES ('admin', '$hashed_password', 'Administrator', 'admin@kiosbungsujaya.com')";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Admin user created<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>admin123</strong><br>";
    } else {
        echo "⚠️ Error creating admin: " . $conn->error . "<br>";
        
        // Coba cara alternatif
        echo "Trying alternative method...<br>";
        $sql = "INSERT INTO admins (username, password) VALUES ('admin', 'admin123')";
        if ($conn->query($sql)) {
            echo "✅ Admin created with simple password<br>";
        }
    }
} else {
    echo "✅ Admin user found<br>";
    
    // Tampilkan data admin
    $row = $result->fetch_assoc();
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>ID</td><td>" . $row['id'] . "</td></tr>";
    echo "<tr><td>Username</td><td>" . $row['username'] . "</td></tr>";
    echo "<tr><td>Password Hash</td><td>" . substr($row['password'], 0, 30) . "...</td></tr>";
    echo "</table>";
    
    // Test password
    $test_password = 'admin123';
    if (password_verify($test_password, $row['password'])) {
        echo "✅ Password 'admin123' VERIFIED<br>";
    } else {
        echo "❌ Password 'admin123' NOT VERIFIED<br>";
        
        // Reset password
        echo "Resetting password...<br>";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE admins SET password = '$new_hash' WHERE username = 'admin'");
        echo "✅ Password reset to 'admin123'<br>";
    }
}

// 6. Buat FILE LOGIN SEDERHANA
echo "<h3>5. Creating simple login test...</h3>";

$simple_login = '<?php
// simple_login.php - Login sederhana
session_start();

// Konfigurasi
$conn = new mysqli("localhost", "root", "", "kios_bungsu_jaya");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Query database
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $row["password"])) {
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_username"] = $username;
            header("Location: admin_simple.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Admin</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #2e7d32; }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .info { text-align: center; margin-top: 15px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 LOGIN ADMIN</h2>
        <?php if(isset($error)) echo "<div class=\"error\">$error</div>"; ?>
        <form method="POST">
            <div class="input-group">
                <label>Username:</label>
                <input type="text" name="username" required value="admin">
            </div>
            <div class="input-group">
                <label>Password:</label>
                <input type="password" name="password" required value="admin123">
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="info">
            Default: admin / admin123
        </div>
    </div>
</body>
</html>';

file_put_contents('simple_login.php', $simple_login);
echo "✅ Simple login page created: <a href='simple_login.php' target='_blank'>simple_login.php</a><br>";

// 7. Buat ADMIN SIMPLE
echo "<h3>6. Creating simple admin dashboard...</h3>";

$simple_admin = '<?php
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
</html>';

file_put_contents('admin_simple.php', $simple_admin);
echo "✅ Simple admin dashboard created: <a href='admin_simple.php' target='_blank'>admin_simple.php</a><br>";

// 8. Buat FILE LOGIN YANG LEBIH SEDERHANA LAGI
echo "<h3>7. Creating SUPER SIMPLE login...</h3>";

$super_simple = '<?php
// super_simple_login.php - Login tanpa database
session_start();

if (isset($_POST["login"])) {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if ($username === "admin" && $password === "admin123") {
        $_SESSION["admin"] = true;
        header("Location: super_simple_admin.php");
        exit;
    } else {
        $error = "Login gagal!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Super Simple</title>
    <style>
        body { font-family: Arial; background: #2e7d32; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: 15px; text-align: center; width: 350px; }
        h2 { color: #2e7d32; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #2e7d32; color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; width: 100%; }
        .error { background: #ffcccc; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 ADMIN LOGIN</h2>
        <?php if(isset($error)) echo "<div class=\"error\">$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" value="admin" required>
            <input type="password" name="password" placeholder="Password" value="admin123" required>
            <button type="submit" name="login">LOGIN</button>
        </form>
        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            Username: admin<br>
            Password: admin123
        </p>
    </div>
</body>
</html>';

file_put_contents('super_simple_login.php', $super_simple);
echo "✅ Super simple login created: <a href='super_simple_login.php' target='_blank'>super_simple_login.php</a><br>";

// 9. Buat SUPER SIMPLE ADMIN
echo "<h3>8. Creating SUPER SIMPLE admin...</h3>";

$super_simple_admin = '<?php
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
</html>';

file_put_contents('super_simple_admin.php', $super_simple_admin);
echo "✅ Super simple admin created: <a href='super_simple_admin.php' target='_blank'>super_simple_admin.php</a><br>";

$conn->close();

echo "<hr>";
echo "<h2>✅ FIX COMPLETED!</h2>";
echo "<p>Sekarang coba login dengan salah satu cara:</p>";
echo "<ol>";
echo "<li><a href='simple_login.php' target='_blank'>simple_login.php</a> - Login dengan database</li>";
echo "<li><a href='super_simple_login.php' target='_blank'>super_simple_login.php</a> - Login TANPA database</li>";
echo "</ol>";

echo "</div>";
?>