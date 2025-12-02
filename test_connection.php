<?php
require_once 'config.php';

echo "<h2>🔧 Test Koneksi Database</h2>";
echo "<div style='font-family: Arial; padding: 20px;'>";

if (testConnection()) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;'>";
    echo "<h3>✅ SEMUA SISTEM BERFUNGSI!</h3>";
    echo "<p>Website siap digunakan.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>❌ ADA MASALAH</h3>";
    echo "<p>Periksa konfigurasi database.</p>";
    echo "</div>";
}

echo "<br>";
echo "<a href='index.html' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ke Website</a> ";
echo "<a href='admin.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ke Admin</a>";
echo "</div>";
?>