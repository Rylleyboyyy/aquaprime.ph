<?php
session_start();
include '../includes/db.php'; 

header('Content-Type: application/json');

$response = array('status' => 'error', 'message' => 'Something went wrong');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('status' => 'redirect', 'message' => 'PLEASE LOG IN FIRST!'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $check_order = $conn->query("SELECT id FROM orders WHERE user_id = '$user_id' AND status = 'Pending'");
    

    if ($check_order->num_rows > 0) {
        $order = $check_order->fetch_assoc();
        $order_id = $order['id'];
    } else {
        $conn->query("INSERT INTO orders (user_id, total_amount, status, order_date) VALUES ('$user_id', 0, 'Pending', NOW())");
        $order_id = $conn->insert_id;
    }

    $check_item = $conn->query("SELECT id, quantity FROM order_items WHERE order_id = '$order_id' AND product_id = '$product_id'");

    if ($check_item->num_rows > 0) {
        $item = $check_item->fetch_assoc();
        $new_qty = $item['quantity'] + $quantity;
        $conn->query("UPDATE order_items SET quantity = '$new_qty' WHERE id = '{$item['id']}'");
    } else {
        $price_query = $conn->query("SELECT price FROM products WHERE id = '$product_id'");
        $prod = $price_query->fetch_assoc();
        $price = $prod['price'];

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }

    $response['status'] = 'success';
    $response['message'] = 'Item successfully added to your cart!';
}

echo json_encode($response);
?>