<?php
// products.php - Get all products
header('Content-Type: application/json');
require_once 'config.php';

$conn = getConnection();

// Get products
$result = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY name");

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => $row['price'],
        'stock' => $row['stock'],
        'category' => $row['category'],
        'image_url' => $row['image_url'] ?: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
    ];
}

echo json_encode(['success' => true, 'products' => $products]);

$conn->close();
?>