<?php 
session_start();
include '../includes/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

//STATUS SA ORDERS
$sales_query = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Completed' AND admin_hidden = 0");
$total_sales = $sales_query->fetch_assoc()['total'] ?? 0;

$processing_query = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Processing' AND admin_hidden = 0");
$processing_count = $processing_query->fetch_assoc()['count'];

// GET ORDERS SA CUSTOMER INFO OG ORDERED ITEMS
$orders_sql = "SELECT o.*, u.fullname, u.address, u.phone,
    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR '<br>') as ordered_items
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.admin_hidden = 0 AND o.status != 'Pending' 
    GROUP BY o.id
    ORDER BY o.order_date DESC
";
$orders_res = $conn->query($orders_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Aqua Prime</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/adminstyle.css">
    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.css">
    <script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
</head>
<body>

<nav class="admin-navbar">
    <div class="admin-header-left">
        <a href="dashboard.php">
            <img src="../assets/img/logo.png" alt="Aqua Prime Logo" style="height: 100px;">
        </a>
        <span class="admin-title">Admin Dashboard</span>
    </div>
    
    <div class="nav-links">
        <a href="javascript:void(0)" onclick="document.getElementById('deleteModal').style.display='flex'" class="btn-delete">
            <i class="fa-solid fa-trash"></i> DELETE
        </a>
        <a href="print_receipt.php" target="_blank" class="btn-print">
            <i class="fa-solid fa-print"></i> PRINT
        </a>
        <a href="../actions/logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to log out?');">
            <i class="fa-solid fa-right-from-bracket"></i> LOGOUT
        </a>
    </div>
</nav>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <h2 style="color: maroon;">DELETE CURRENT RECORDS?</h2>
        <p style="margin-bottom: 20px; color: #555;">This will clear all records from dashboard.</p>
        
        <form action="truncate_orders.php" method="POST">
            <div style="text-align: left; margin-bottom: 15px;">
                <label style="font-weight: bold;">Enter Admin Password:</label>
                <input type="password" name="admin_password" required 
                       style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('deleteModal').style.display='none'" 
                        style="padding: 10px 20px; border: none; background: gray; color: white; cursor: pointer; border-radius: 5px;">
                    Cancel
                </button>
                <button type="submit" class="btn-delete">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-container">
    
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: green;">
            <div class="stat-info">
                <h2>₱ <?php echo number_format($total_sales, 2); ?></h2>
                <p>TOTAL REVENUE</p>
            </div>
            <i class="fa-solid fa-sack-dollar stat-icon" style="color: green;"></i>
        </div>
        
        <div class="stat-card" style="border-left-color: orange;">
            <div class="stat-info">
                <h2 style="color: orange;"><?php echo $processing_count; ?></h2>
                <p>PROCESSING ORDERS</p>
            </div>
            <i class="fa-solid fa-clock stat-icon" style="color: orange;"></i>
        </div>
    </div>

    <h3 style="font-size: 25px; color: rgb(0, 115, 187);">
        <i class="fa-solid fa-list-check"></i> Manage Orders
    </h3>
    
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 200px;">Customer Info</th>  
                    <th>Delivery Address</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Order Details</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>

            <tbody>
                <?php while($order = $orders_res->fetch_assoc()): ?>
                <tr>
                    <td style="line-height: 1.5;">
                        <div style="font-weight: bold; color: green; font-size: 1.1rem;">
                            <i class="fa-solid fa-user" style="margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($order['fullname']); ?>
                        </div>
                        <div style="font-size: 0.9rem; color: #555; margin-top: 4px;">
                            <i class="fa-solid fa-phone" style="margin-right: 6px; font-size: 0.8rem;"></i>
                            <?php echo htmlspecialchars($order['phone']); ?>
                        </div>
                    </td>

                    <td style="max-width: 250px; line-height: 1.4; font-size: 0.9rem;">
                        <i class="fa-solid fa-location-dot" style="color: red;"></i>
                        <?php echo htmlspecialchars($order['address']); ?>
                    </td>

                    <td style="color: green; font-weight: bold; font-size: 0.9rem;">
                        ₱ <?php echo number_format($order['total_amount'], 2); ?>
                    </td>

                    <td style="font-size: 0.9rem; color: #666;">
                        <?php echo date("M d, Y", strtotime($order['order_date'])); ?><br>
                        <small><?php echo date("h:i A", strtotime($order['order_date'])); ?></small>
                    </td>

                    <td style="font-size: 0.95rem; color: #333; line-height: 1.6; font-weight: bold ;">
                        <?php echo !empty($order['ordered_items']) ? $order['ordered_items'] : '<span style="color:gray; font-style:italic;">No items found</span>'; ?>
                    </td>

                    <td>
                        <span class="status-badge <?php echo strtolower($order['status']); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    
                    <td>
                    <?php if ($order['status'] == 'Completed' || $order['status'] == 'Cancelled'): ?>
                        <div style="color: <?php echo ($order['status'] == 'Cancelled') ? '#ff4d4d' : 'orange'; ?>; font-weight: bold; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-lock"></i>Locked
                        </div>
                    <?php else: ?>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <form action="update_status.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel their order?');">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="Cancelled">
                                <button type="submit" class="btn-quick" title="Cancel Order" style="background: maroon; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                            <form action="update_status.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="Delivering">
                                <button type="submit" class="btn-quick" title="Mark as Delivering" style="background: navy; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">
                                    <i class="fa-solid fa-truck"></i>
                                </button>
                            </form>
                            <form action="update_status.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="Completed">
                                <button type="submit" class="btn-quick" title="Mark as Paid" style="background: green; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">
                                    <i class="fa-solid fa-check-double"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <h3 style="font-size: 25px; color: rgb(0, 115, 187); margin-top: 50px; margin-bottom: 20px;">
        <i class="fa-solid fa-message"></i> Customer Chat Support
    </h3>

    <div class="chat-container">
        
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-users" style="color: rgb(0, 115, 187);"></i>
                <h3>INBOX</h3>
            </div>
            <div class="sidebar-list" id="conversation-list">
                </div>
        </div>
        
        <div class="chat-area">
            <div class="chat-header" id="active-chat-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-regular fa-comments" style="font-size: 24px; color: rgb(0, 115, 187);"></i>
                    <span style="color: Black;">SELECT A CONVERSATION TO THE LEFT</span>
                </div>
            </div>
            
            <div class="chat-box" id="admin-chat-box">
                <div style="text-align: center; color: gray; margin-top: 150px;">
                    <i class="fa-solid fa-inbox" style="font-size: 60px; margin-bottom: 15px;"></i>
                    <p style="font-size: 16px; font-weight: 500;">Your messages will appear here.</p>
                </div>
            </div>
            
            <div class="chat-input" id="admin-chat-input" style="display: none;">
                <input type="hidden" id="active-customer-id">
                <input type="text" id="admin-message" placeholder="Type your reply to the customer...">
                <button onclick="sendAdminMessage()"><i class="fa-solid fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener("load", function() {
        const loader = document.getElementById("loader-wrapper");
        
        setTimeout(() => {
            if(loader) {
                loader.classList.add("loader-hidden");

                setTimeout(() => loader.style.display = 'none', 400);
            }
        }, 1000);
    });
</script>

<script>
    let activeCustomerId = null;
    let previousActiveMessageCount = 0; 
    let userLastMessageTimes = {}; 
    let isFirstLoad = true; 
    
    const notificationSound = new Audio('../assets/audio/notification.mp3'); 

    document.body.addEventListener('click', function unlockAudio() {
        notificationSound.play().then(() => {
            notificationSound.pause();
            notificationSound.currentTime = 0;
        }).catch(() => {}); 
    }, { once: true }); 

    function formatTime(dateString) {
        const options = { hour: 'numeric', minute: '2-digit', hour12: true, month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleTimeString('en-US', options);
    }

    function loadConversations() {
        fetch('../chat_handler.php?action=fetch_conversations&_t=' + Date.now()) 
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('conversation-list');
                list.innerHTML = ''; 
                
                if (data.length === 0) {
                    list.innerHTML = '<p style="text-align:center; color: white; margin-top:20px;">No messages yet.</p>';
                    isFirstLoad = false;
                    return;
                }

                let shouldPlaySound = false;

                data.forEach(user => {
                    let lastKnownTime = userLastMessageTimes[user.customer_id];
                    let incomingTime = user.latest_msg;
                    let unreadCount = parseInt(user.unread_count) || 0;

                    if (!isFirstLoad) {
                        if (lastKnownTime === undefined && unreadCount > 0) {
                            shouldPlaySound = true; 
                        } else if (lastKnownTime !== undefined && incomingTime !== lastKnownTime && unreadCount > 0) {
                            shouldPlaySound = true; 
                        }
                    }
                    
                    userLastMessageTimes[user.customer_id] = incomingTime;

                    const div = document.createElement('div');
                    const isActive = activeCustomerId === user.customer_id ? 'active' : '';
                    div.className = `customer-item ${isActive}`;
                    
                    let badgeHtml = '';
                    if (unreadCount > 0) {
                        badgeHtml = `<span class="unread-badge">${unreadCount}</span>`;
                    }

                    div.innerHTML = `
                        <div class="customer-info">
                            <i class="fa-solid fa-user"></i> 
                            <span>${user.fullname || user.firstname}</span>
                        </div>
                        ${badgeHtml}
                    `;
                    div.onclick = () => openChat(user.customer_id, user.fullname || user.firstname);
                    list.appendChild(div);
                });
                
                if (shouldPlaySound) {
                    notificationSound.play().catch(e => console.error("Audio blocked! Click the page first.", e));
                }
                
                isFirstLoad = false; 
            })
            .catch(err => console.error("Error loading conversations", err));
    }
    function openChat(customerId, customerName) {
        activeCustomerId = customerId;
        previousActiveMessageCount = 0; 
        document.getElementById('active-customer-id').value = customerId;
        
        
        document.getElementById('active-chat-header').innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-user" style="font-size: 28px; color: rgb(0, 115, 187);"></i>
                <div>
                    <div style="line-height: 1;">${customerName}</div>
                </div>
            </div>
            <button onclick="deleteConversation(${customerId})" class="btn-delete-chat">
                <i class="fa-solid fa-trash"></i> DELETE CHAT
            </button>
        `;
        
        document.getElementById('admin-chat-input').style.display = 'flex';
        loadConversations();
        loadAdminMessages();
    }
    function deleteConversation(customerId) {
        if(confirm("Are you sure you want to permanently delete this conversation? This action cannot be undone.")) {
            const formData = new FormData();
            formData.append('action', 'delete_conversation');
            formData.append('customer_id', customerId);

            fetch('../chat_handler.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        activeCustomerId = null;
                        document.getElementById('active-chat-header').innerHTML = `
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i class="fa-regular fa-comments" style="font-size: 24px; color: rgb(0, 115, 187);"></i>
                                <span style="color: Black;">SELECT A CONVERSATION TO THE LEFT</span>
                            </div>
                        `;
                        document.getElementById('admin-chat-box').innerHTML = `
                            <div style="text-align: center; color: gray; margin-top: 150px;">
                                <i class="fa-solid fa-inbox" style="font-size: 60px; margin-bottom: 15px;"></i>
                                <p style="font-size: 16px; font-weight: 500;">Your messages will appear here.</p>
                            </div>
                        `;
                        document.getElementById('admin-chat-input').style.display = 'none';
                        loadConversations();
                    } else {
                        alert("Failed to delete conversation.");
                    }
                });
        }
    }

    function loadAdminMessages() {
        if (!activeCustomerId) return;
        
        fetch(`../chat_handler.php?action=fetch_messages&customer_id=${activeCustomerId}&viewer=admin&_t=` + Date.now())
            .then(res => res.json())
            .then(data => {
                const box = document.getElementById('admin-chat-box');
                
                if (data.length > previousActiveMessageCount && previousActiveMessageCount !== 0) {
                    const lastMsg = data[data.length - 1];
                    if (lastMsg.sender === 'customer') {
                        notificationSound.play().catch(e => console.error("Audio blocked:", e));
                    }
                }
                previousActiveMessageCount = data.length; 

                box.innerHTML = ''; 
                
                data.forEach(msg => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'msg-wrapper ' + msg.sender;
                    
                    const timeStr = formatTime(msg.created_at);
                    
                    let statusHtml = '';
                    if (msg.sender === 'admin') {
                        if (msg.is_read == 1) {
                            statusHtml = `<span class="status-seen"><i class="fa-solid fa-check-double"></i> Seen</span>`;
                        } else {
                            statusHtml = `<span class="status-sent"><i class="fa-solid fa-check"></i> Sent</span>`;
                        }
                    }
                    
                    wrapper.innerHTML = `
                        <div class="msg">${msg.message}</div>
                        <div class="msg-meta">
                            ${msg.sender === 'admin' ? statusHtml + ' • ' : ''}
                            <span>${timeStr}</span>
                        </div>
                    `;
                    box.appendChild(wrapper);
                });
                box.scrollTop = box.scrollHeight; 
            })
            .catch(err => console.error("Error loading messages", err));
    }

    function sendAdminMessage() {
        if (!activeCustomerId) return;
        const input = document.getElementById('admin-message');
        const text = input.value.trim();
        if (text === '') return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('customer_id', activeCustomerId);
        formData.append('sender', 'admin');
        formData.append('message', text);

        input.value = ''; 

        fetch('../chat_handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    loadAdminMessages();
                }
            });
    }

    document.getElementById("admin-message").addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendAdminMessage();
        }
    });

    loadConversations();
    setInterval(() => {
        loadConversations();
        if (activeCustomerId) loadAdminMessages();
    }, 3000);
</script>

</body>
</html>