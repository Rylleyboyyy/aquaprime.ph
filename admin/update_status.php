<?php
session_start();
include '../includes/db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $check_query = $conn->query("SELECT o.status, o.user_id, o.total_amount, u.email, u.fullname 
                                 FROM orders o 
                                 JOIN users u ON o.user_id = u.id 
                                 WHERE o.id = '$order_id'");
    
    if ($check_query && $check_query->num_rows > 0) {
        $order_data = $check_query->fetch_assoc();
        $current_status = $order_data['status'];
        $customer_id = $order_data['user_id'];
        
        $customer_email = $order_data['email'];
        $customer_name = $order_data['fullname'];
        $formatted_price = number_format($order_data['total_amount'], 2);

        if ($current_status !== $new_status) {
            $update_sql = "UPDATE orders SET status = '$new_status' WHERE id = '$order_id'";
            
            if ($conn->query($update_sql)) {
                
                if ($new_status === 'Delivering' || $new_status === 'Completed' || $new_status === 'Cancelled') {
                    
                    $auto_message = "";
                    $email_subject = "";
                    $message_text = "";
                    
                    // AUTOMATIC STATUS DETAILS
                    if ($new_status === 'Delivering') {
                        $auto_message = "Your AquaPrime order is on its way! Please wait patiently and pay the delivery rider the said amount of ₱" . $formatted_price . ". 🚚🌫";
                        
                        $email_subject = "AquaPrime: Your Order is Out for Delivery!";
                        $message_text = "Your AquaPrime order is now <b>OUT FOR DELIVERY</b>. Please expect our rider at your doorstep shortly. Amount to pay: <b>₱{$formatted_price}</b>.";
                    
                    } elseif ($new_status === 'Completed') {
                        $auto_message = "Thank you for your purchase! Your AquaPrime order has been delivered and your payment of ₱" . $formatted_price . " has been received. Enjoy your AquaPrime water!✅";
                        
                        $email_subject = "AquaPrime: Order Completed & Paid!";
                        $message_text = "Your AquaPrime order has been marked as <b>PAID</b> and your payment of <b>₱{$formatted_price}</b> has been received.";
                        
                    } elseif ($new_status === 'Cancelled') {
                        $auto_message = "We're sorry, but your AquaPrime order has been cancelled. If you believe this is a mistake or have any questions, please reply to this message. ❌";
                        
                        $email_subject = "AquaPrime: Order Cancelled";
                        $message_text = "We regret to inform you that your AquaPrime order <b>₱{$formatted_price}</b> has been <b>CANCELLED</b>. If you believe this was a mistake or have any questions, please contact our support team via the chat on our website or just call/text <b>09952729291</b>.";
                    }
                    
                    // --- EMAIL DESIGN
                    $email_body = '
                    <div style="background-color: #f0f4f8; padding: 40px 20px; font-family: Arial, sans-serif;">
                        <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center;">
                            
                            <img src="cid:aquaprime_logo" alt="AquaPrime Logo" style="max-width: 120px; margin-bottom: 20px;">
                            
                            <h2 style="color: #0073bb; margin-bottom: 20px;">Order Update</h2>
                            
                            <p style="font-size: 16px; color: #444; text-align: left; line-height: 1.5;">Hello <b>' . $customer_name . '</b>,</p>
                            
                            <p style="font-size: 16px; color: #444; text-align: left; line-height: 1.5;">' . $message_text . '</p>
                            
                            <hr style="border: none; border-top: 2px dashed #eee; margin: 25px 0;">
                            
                            <p style="font-size: 14px; color: navy;">Thank you for choosing AquaPrime! 💧</p>
                        </div>
                    </div>';

                    // --- SEND THE AUTOMATIC CHAT ---
                    if (!empty($auto_message)) {
                        $safe_message = $conn->real_escape_string($auto_message);
                        $chat_sql = "INSERT INTO chat_messages (customer_id, sender, message, is_read) 
                                     VALUES ('$customer_id', 'admin', '$safe_message', 0)";
                        $conn->query($chat_sql);
                    }
                    if (!empty($email_subject) && !empty($customer_email)) {
                        $mail = new PHPMailer(true);
                        try {                                          
                            $mail->isSMTP();                                            
                            $mail->Host       = 'smtp.gmail.com';                     
                            $mail->SMTPAuth   = true;                                   
                            $mail->Username   = 'abdulmalikwd2@gmail.com';   
                            $mail->Password   = 'jwryizmxrtcbrdbs';                            
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
                            $mail->Port       = 465;                                    

                            $mail->setFrom('abdulmalikwd2@gmail.com', 'AquaPrime');
                            $mail->addAddress($customer_email, $customer_name);     
                            $mail->addEmbeddedImage('../assets/img/logo.png', 'aquaprime_logo');

                            $mail->isHTML(true);                                  
                            $mail->Subject = $email_subject;
                            $mail->Body    = $email_body;

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Mailer Error: {$mail->ErrorInfo}");
                        }
                    }
                }
            }
        }
    }
    header("Location: dashboard.php");
    exit();
}
?>