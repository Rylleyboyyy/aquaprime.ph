<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id']) && isset($_POST['quantity'])) {
    
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];

    // SECURITY CHECK 
    $check = $conn->query("SELECT oi.id FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.id 
                           WHERE oi.id = '$item_id' AND o.user_id = '$user_id' AND o.status = 'Pending'");

    if ($check->num_rows > 0 && $quantity > 0) {
        // PARA MA UPDATE ANG QUANTITY SA ITEM
        if ($conn->query("UPDATE order_items SET quantity = '$quantity' WHERE id = '$item_id'")) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB Error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    }
}
?>