<?php
// process_order.php - Handle order submission
header('Content-Type: application/json');
require_once 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['product_id', 'product_name', 'product_price', 'quantity', 
             'customer_name', 'whatsapp', 'address', 'delivery_date', 'payment_method'];

foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit;
    }
}

// Sanitize data
$product_id = intval($data['product_id']);
$product_name = htmlspecialchars($data['product_name']);
$product_price = intval($data['product_price']);
$quantity = intval($data['quantity']);
$customer_name = htmlspecialchars($data['customer_name']);
$whatsapp = preg_replace('/[^0-9]/', '', $data['whatsapp']);
$address = htmlspecialchars($data['address']);
$delivery_date = $data['delivery_date'];
$payment_method = $data['payment_method'];

// Validate WhatsApp number
if (strlen($whatsapp) < 10 || strlen($whatsapp) > 15) {
    echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tidak valid']);
    exit;
}

// Calculate total
$total_price = $product_price * $quantity;

// Generate order number
$order_number = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Get database connection
$conn = getConnection();

// Check stock
$stock_check = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stock_check->bind_param("i", $product_id);
$stock_check->execute();
$stock_result = $stock_check->get_result();
$stock_row = $stock_result->fetch_assoc();

if ($stock_row['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
    $conn->close();
    exit;
}

// Insert order
$stmt = $conn->prepare("INSERT INTO orders (
    order_number, product_id, product_name, product_price, quantity, total_price,
    customer_name, whatsapp, address, delivery_date, payment_method
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sisiiisssss",
    $order_number, $product_id, $product_name, $product_price, $quantity, $total_price,
    $customer_name, $whatsapp, $address, $delivery_date, $payment_method
);

if ($stmt->execute()) {
    // Update stock
    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $update_stock->bind_param("ii", $quantity, $product_id);
    $update_stock->execute();
    
    // Create WhatsApp message
    $whatsapp_message = "Halo *" . $customer_name . "*%0A%0A" .
                       "Terima kasih telah berbelanja di *KIOS BUNGSU JAYA*%0A%0A" .
                       "*📋 DETAIL PESANAN*%0A" .
                       "🔢 No. Pesanan: *" . $order_number . "*%0A" .
                       "📦 Produk: *" . $product_name . "*%0A" .
                       "⚖️ Jumlah: *" . $quantity . " liter*%0A" .
                       "💰 Harga: Rp " . number_format($product_price, 0, ',', '.') . "/liter%0A" .
                       "💵 Total: *Rp " . number_format($total_price, 0, ',', '.') . "*%0A" .
                       "💳 Metode: " . ($payment_method == 'cod' ? 'COD' : 'Dana') . "%0A" .
                       "📅 Kirim: " . $delivery_date . "%0A" .
                       "🏠 Alamat: " . $address . "%0A%0A";
    
    if ($payment_method == 'dana') {
        $whatsapp_message .= "*💰 PEMBAYARAN DANA*%0A" .
                           "Transfer ke: *0857-7498-8295*%0A" .
                           "a.n. KIOS BUNGSU JAYA%0A%0A";
    }
    
    $whatsapp_message .= "📞 CS: 0857-7498-8295%0A" .
                       "Terima kasih 🙏%0A" .
                       "*KIOS BUNGSU JAYA*";
    
    $whatsapp_url = "https://wa.me/" . $whatsapp . "?text=" . urlencode($whatsapp_message);
    
    // Response
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil disimpan!',
        'order_number' => $order_number,
        'whatsapp_url' => $whatsapp_url,
        'total_price' => number_format($total_price, 0, ',', '.')
    ]);
    
    // Log to file (backup)
    $log_entry = date('Y-m-d H:i:s') . " | " . $order_number . " | " . $customer_name . " | " . 
                 $whatsapp . " | " . $product_name . " x" . $quantity . " | Rp " . 
                 number_format($total_price) . "\n";
    file_put_contents('orders_log.txt', $log_entry, FILE_APPEND);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan pesanan: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>