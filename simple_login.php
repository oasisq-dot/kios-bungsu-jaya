<?php
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
</html>