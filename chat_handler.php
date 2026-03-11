<?php
session_start();
include 'includes/db.php'; 

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'send_message') {
    $customer_id = $_POST['customer_id'];
    $sender = $_POST['sender']; 
    $message = $conn->real_escape_string($_POST['message']);

    if (!empty($message)) {
        $sql = "INSERT INTO chat_messages (customer_id, sender, message, is_read) VALUES ('$customer_id', '$sender', '$message', 0)";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'error' => $conn->error]);
        }
    }
    exit;
}

if ($action == 'fetch_messages') {
    $customer_id = $_GET['customer_id'];
    $viewer = $_GET['viewer'] ?? ''; 

    if ($viewer == 'customer') {
        $conn->query("UPDATE chat_messages SET is_read = 1 WHERE customer_id = '$customer_id' AND sender = 'admin' AND is_read = 0");
    } elseif ($viewer == 'admin') {
        $conn->query("UPDATE chat_messages SET is_read = 1 WHERE customer_id = '$customer_id' AND sender = 'customer' AND is_read = 0");
    }

    $sql = "SELECT * FROM chat_messages WHERE customer_id = '$customer_id' ORDER BY created_at ASC";
    $result = $conn->query($sql);
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages);
    exit;
}

if ($action == 'get_unread_count') {
    $customer_id = $_GET['customer_id'];
    $sql = "SELECT COUNT(*) as unread FROM chat_messages WHERE customer_id = '$customer_id' AND sender = 'admin' AND is_read = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo json_encode(['unread' => $row['unread']]);
    exit;
}

if ($action == 'fetch_conversations') {
    $sql = "SELECT u.id AS customer_id, u.fullname,(SELECT COUNT(*) FROM chat_messages WHERE customer_id = u.id AND sender = 'customer' AND is_read = 0) AS unread_count,MAX(c.created_at) AS latest_msg
        FROM users u
        JOIN chat_messages c ON u.id = c.customer_id
        GROUP BY u.id, u.fullname
        ORDER BY latest_msg DESC
    ";
    $result = $conn->query($sql);
    
    $conversations = [];
    if($result) {
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
    }
    echo json_encode($conversations);
    exit;
}

if ($_POST['action'] === 'delete_conversation') {
    $customer_id = $conn->real_escape_string($_POST['customer_id']);
    
    $delete_query = "DELETE FROM chat_messages WHERE customer_id = '$customer_id'";
    
    if ($conn->query($delete_query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit();
}
?>