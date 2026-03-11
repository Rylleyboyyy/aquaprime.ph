<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // DILI PWEDE MAG DUPLICATE EMAIL
        echo "<script>alert('This Email Address is already registered. Please use other email.'); window.location.href='../register.php';</script>";
    } else {
        
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $password, $phone, $address);
        
        if ($stmt->execute()) {
            echo "<script>alert('Account created! You can now login with your Name.'); window.location.href='../login.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>