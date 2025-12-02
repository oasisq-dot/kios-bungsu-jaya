<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

require_once 'config.php';
$conn = getConnection();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="pesanan_kios_bungsu_jaya.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['No Pesanan', 'Tanggal', 'Pelanggan', 'WhatsApp', 'Produk', 'Harga', 'Jumlah', 'Total', 'Alamat', 'Pengiriman', 'Metode', 'Status']);

$result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['order_number'],
        $row['order_date'],
        $row['customer_name'],
        $row['whatsapp'],
        $row['product_name'],
        $row['product_price'],
        $row['quantity'],
        $row['total_price'],
        $row['address'],
        $row['delivery_date'],
        $row['payment_method'],
        $row['order_status']
    ]);
}

fclose($output);
$conn->close();
?>