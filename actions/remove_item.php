<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $item_id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT oi.id FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.id 
                           WHERE oi.id = '$item_id' AND o.user_id = '$user_id' AND o.status = 'Pending'");

    if ($check->num_rows > 0) {
        if ($conn->query("DELETE FROM order_items WHERE id = '$item_id'")) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found or cannot be deleted']);
    }
}
?>