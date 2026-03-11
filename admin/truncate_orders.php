<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_SESSION['user_id'];
    $password_input = $_POST['admin_password'];

    // PASSWORD VERIFICATION BEFORE MAKA DELETE
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (password_verify($password_input, $admin['password'])) {
        
        $conn->query("UPDATE orders SET status = 'Cancelled' WHERE status IN ('Processing', 'OutofDelivery')");

        $conn->query("UPDATE orders SET admin_hidden = 1 WHERE status != 'Pending'");

        echo "<script>alert('Dashboard Cleared!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Incorrect Admin Password!'); window.location.href='dashboard.php';</script>";
    }
}
?>