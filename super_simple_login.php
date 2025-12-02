<?php
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
</html>