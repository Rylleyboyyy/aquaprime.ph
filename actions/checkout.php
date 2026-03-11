<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $total_amount = $_POST['total_amount'];

    $stmt = $conn->prepare("UPDATE orders SET status = 'Processing', total_amount = ? WHERE id = ?");
    $stmt->bind_param("di", $total_amount, $order_id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Order Placed Successfully! We will contact you shortly.');
            window.location.href = '../my_orders.php';
        </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>