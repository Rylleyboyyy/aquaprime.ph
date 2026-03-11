<?php
session_start();
include '../includes/db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $order_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT id FROM orders WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'Processing'");

    if ($check->num_rows > 0) {
        $conn->query("UPDATE orders SET status = 'Cancelled' WHERE id = '$order_id'");
        echo "<script>alert('ORDER HAS BEEN CANCELLED.'); window.location.href='../my_orders.php';</script>";
    } else {
        echo "<script>alert('Cannot cancel this order.'); window.location.href='../my_orders.php';</script>";
    }
} else {
    header("Location: ../my_orders.php");
}
?>