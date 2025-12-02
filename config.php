<?php
// config.php - Konfigurasi Database

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kios_bungsu_jaya');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        // Jika gagal, coba buat database
        createDatabase();
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Create database if not exists
function createDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    $conn->select_db(DB_NAME);
    
    // Create tables
    createTables($conn);
    
    $conn->close();
}

// Create tables
function createTables($conn) {
    // Products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price INT NOT NULL,
        stock INT DEFAULT 100,
        image_url VARCHAR(255),
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating products table: " . $conn->error);
    }
    
    // Insert sample products if empty
    $check = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $check->fetch_assoc();
    
    if ($row['count'] == 0) {
        $products = [
            ['Beras Premium', 'Beras putih pulen dengan aroma wangi, tekstur lembut', 14000, 100, 'beras'],
            ['Beras Lele', 'Beras dengan butiran sedang, tidak mudah basi', 13000, 150, 'beras'],
            ['Beras Sakura', 'Beras dengan aroma khas, pulen', 12500, 120, 'beras'],
            ['Beras Jempol OK', 'Beras berkualitas dengan harga ekonomis', 11500, 200, 'beras'],
            ['Ketan Putih', 'Ketan putih pulen untuk lemper, wajik', 12000, 80, 'ketan'],
            ['Ketan Hitam', 'Ketan hitam organik untuk bubur ketan', 15000, 60, 'ketan'],
            ['Beras Merah', 'Beras merah organik kaya serat', 16000, 70, 'beras']
        ];
        
        foreach ($products as $product) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiis", $product[0], $product[1], $product[2], $product[3], $product[4]);
            $stmt->execute();
        }
    }
    
    // Orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        product_id INT NOT NULL,
        product_name VARCHAR(100) NOT NULL,
        product_price INT NOT NULL,
        quantity INT NOT NULL,
        total_price INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        delivery_date DATE NOT NULL,
        payment_method VARCHAR(20) NOT NULL,
        order_status VARCHAR(50) DEFAULT 'pending',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating orders table: " . $conn->error);
    }
    
    // Admins table
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(100)
    )";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating admins table: " . $conn->error);
    }
    
    // Insert default admin if not exists
    $check = $conn->query("SELECT COUNT(*) as count FROM admins WHERE username = 'admin'");
    $row = $check->fetch_assoc();
    
    if ($row['count'] == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", 'admin', $hashed_password, 'Administrator', 'admin@kiosbungsujaya.com');
        $stmt->execute();
    }
}

// Test connection
function testConnection() {
    $conn = getConnection();
    if ($conn) {
        echo "✅ Database connected successfully!<br>";
        
        // Test products count
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch_assoc();
        echo "📦 Products in database: " . $row['count'] . "<br>";
        
        $conn->close();
        return true;
    }
    return false;
}
?>