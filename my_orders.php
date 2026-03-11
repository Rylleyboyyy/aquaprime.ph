<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

$cart_query = $conn->query("SELECT * FROM orders WHERE user_id = '$user_id' AND status = 'Pending'");
$cart = $cart_query->fetch_assoc();
$cart_id = $cart ? $cart['id'] : 0;

$items_res = null; 
$grand_total = 0;

if ($cart_id > 0) {
    $items_sql = "SELECT oi.*, p.name, p.image_path 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = '$cart_id'";
    $items_res = $conn->query($items_sql);
}
?>

<div class="container" style="min-height: 80vh;">
    
    <h1 style="color: var(--primary-blue); margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        <i class="fa-solid fa-cart-shopping" style="color: green;"></i> My Cart
    </h1>

    <div class="cart-section">
        <h3 style="color: #555; margin-bottom: 15px;">Current Order</h3>

        <?php if ($cart_id > 0 && isset($items_res) && $items_res->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items_res->fetch_assoc()): 
                            $subtotal = $item['price_at_time'] * $item['quantity'];
                            $grand_total += $subtotal;
                            $img = !empty($item['image_path']) ? $item['image_path'] : 'default.png';
                        ?>
                        <tr id="row-<?php echo $item['id']; ?>">
                            <td class="product-col">
                                <img src="assets/img/<?php echo $img; ?>" alt="img">
                                <span><?php echo $item['name']; ?></span>
                            </td>
                            
                            <td class="price-col" data-price="<?php echo $item['price_at_time']; ?>">
                                ₱ <?php echo number_format($item['price_at_time'], 2); ?>
                            </td>
                            
                            <td>
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" onclick="changeQty(<?php echo $item['id']; ?>, -1)">-</button>
                                    <span id="qty-<?php echo $item['id']; ?>" class="qty-number"><?php echo $item['quantity']; ?></span>
                                    <button type="button" class="qty-btn" onclick="changeQty(<?php echo $item['id']; ?>, 1)">+</button>
                                </div>
                            </td>

                            <td style="color: green; font-weight: bold;">
                                ₱ <span class="row-subtotal" id="subtotal-<?php echo $item['id']; ?>"><?php echo number_format($subtotal, 2); ?></span>
                            </td>

                            <td style="text-align: center;">
                                <button type="button" class="btn-remove" onclick="removeItem(this, <?php echo $item['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <h3>Total: <span style="color: var(--aqua-green);">₱ <span id="cart-total"><?php echo number_format($grand_total, 2); ?></span></span></h3>
                
                <form action="actions/checkout.php" method="POST" onsubmit="return confirm('Are you sure you want to place this order?');">
                    <input type="hidden" name="order_id" value="<?php echo $cart_id; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
                    <button type="submit" class="btn-checkout">
                        CHECK OUT ORDER <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-basket-shopping"></i>
                <p>Your cart is empty.</p>
                <a href="products.php" class="btn-main">Go to Products</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="history-section" style="margin-top: 50px;">
        <h1 style="color: var(--primary-blue); margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        <i class="fa-solid fa-book" style="color: green;"></i> Order History
        </h1>
        
        <?php
        $history_query = $conn->query("SELECT * FROM orders WHERE user_id = '$user_id' AND status != 'Pending' ORDER BY order_date DESC");
        
        if ($history_query->num_rows > 0):
        ?>
            <table class="history-table">
                <thead>
                    <tr><th>Order Date</th><th>Total Amount</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($hist = $history_query->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date("M d, Y", strtotime($hist['order_date'])); ?></td>
                        <td style="color: green;">₱ <?php echo number_format($hist['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge <?php echo strtolower($hist['status']); ?>">
                                <?php echo $hist['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($hist['status'] == 'Processing'): ?>
                                <a href="actions/cancel_order.php?id=<?php echo $hist['id']; ?>" 
                                   class="btn-cancel-order"
                                   onclick="return confirm('Are you sure you want to cancel this order?');">
                                   Cancel Order
                                </a>
                            <?php else: ?>
                                <span style="color: #aaa; font-size: 0.8rem;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #888;">No past orders found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function changeQty(itemId, change) {
    let qtySpan = document.getElementById('qty-' + itemId);
    let currentQty = parseInt(qtySpan.innerText);
    let newQty = currentQty + change;
    if (newQty < 1) return; 

    qtySpan.innerText = newQty;
    updateRowPrice(itemId, newQty);

    let formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('quantity', newQty);

    fetch('actions/update_quantity.php', { method: 'POST', body: formData })
    .then(res => res.json()).then(data => {
        if(data.status !== 'success') {
            alert('Error updating cart');
            qtySpan.innerText = currentQty; 
            updateRowPrice(itemId, currentQty);
        }
    });
}

function updateRowPrice(itemId, quantity) {
    let row = document.getElementById('row-' + itemId);
    let price = parseFloat(row.querySelector('.price-col').getAttribute('data-price'));
    let newSubtotal = price * quantity;
    
    document.getElementById('subtotal-' + itemId).innerText = newSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    recalculateGrandTotal();
}

function recalculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.row-subtotal').forEach(span => {
        let val = parseFloat(span.innerText.replace(/,/g, ''));
        total += val;
    });

    document.getElementById('cart-total').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    let hiddenInput = document.querySelector('input[name="total_amount"]');
    if(hiddenInput) hiddenInput.value = total;
}

// --- Remove Item ---
function removeItem(btn, itemId) {
    if(!confirm('Are you sure you want to remove this item?')) return;

    let formData = new FormData();
    formData.append('id', itemId);

    fetch('actions/remove_item.php', { method: 'POST', body: formData })
    .then(response => response.json()).then(data => {
        if (data.status === 'success') {
            let row = btn.closest('tr');
            row.style.transition = "all 0.5s ease";
            row.style.opacity = "0";
            row.style.transform = "translateX(50px)";
            setTimeout(() => {
                row.remove();
                recalculateGrandTotal(); 
                let tbody = document.querySelector('.cart-table tbody');
                if (tbody && tbody.children.length === 0) location.reload();
            }, 500);
        } else {
            alert("Error: " + data.message);
        }
    });
}
</script>

<button id="chat-toggle-btn" onclick="toggleChat()">
    <i class="fa-solid fa-comment-dots"></i>
    <div id="unread-badge">0</div>
</button>
<div id="chat-widget">
    <div id="chat-header" onclick="toggleChat()">
        <span><i class="fa-solid fa-headset" style="margin-right: 8px;"></i> AquaPrime Chat Support</span>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
    <div id="chat-box"></div>
    <div id="chat-input-area">
        <input type="text" id="chat-message" placeholder="Type a message...">
        <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<script>
    const customerId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    let chatOpen = false;
    
    let previousUnreadCount = 0; 
    const notificationSound = new Audio('assets/audio/notification.mp3'); 

    function toggleChat() {
        const widget = document.getElementById('chat-widget');
        const badge = document.getElementById('unread-badge');
        
        chatOpen = !chatOpen;
        
        if (chatOpen) {
            widget.style.display = 'block';
            badge.style.display = 'none';
            previousUnreadCount = 0;
            loadMessages();
            window.chatInterval = setInterval(loadMessages, 3000); 
        } else {
            widget.style.display = 'none';
            clearInterval(window.chatInterval);
            checkUnreadCount();
        }
    }

    function formatTime(dateString) {
        const options = { hour: 'numeric', minute: '2-digit', hour12: true, month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleTimeString('en-US', options);
    }

    function loadMessages() {
        if (!customerId) return;
        
        fetch(`chat_handler.php?action=fetch_messages&customer_id=${customerId}&viewer=customer`)
            .then(res => res.json())
            .then(data => {
                const box = document.getElementById('chat-box');
                box.innerHTML = '';
                
                data.forEach(msg => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'msg-wrapper ' + msg.sender;
                    
                    const timeStr = formatTime(msg.created_at);
                    
                    let statusHtml = '';
                    if (msg.sender === 'customer') {
                        if (msg.is_read == 1) {
                            statusHtml = `<span class="msg-status status-seen"><i class="fa-solid fa-check-double"></i> Seen</span>`;
                        } else {
                            statusHtml = `<span class="msg-status status-sent"><i class="fa-solid fa-check"></i> Sent</span>`;
                        }
                    }

                    wrapper.innerHTML = `
                        <div class="msg">${msg.message}</div>
                        <div class="msg-meta">
                            ${msg.sender === 'customer' ? statusHtml + ' • ' : ''}
                            <span>${timeStr}</span>
                        </div>
                    `;
                    box.appendChild(wrapper);
                });
                box.scrollTop = box.scrollHeight;
            });
    }

    function sendMessage() {
        if (!customerId) { alert("Please LOG IN first to chat."); return; }
        const input = document.getElementById('chat-message');
        const text = input.value.trim();
        if (text === '') return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('customer_id', customerId);
        formData.append('sender', 'customer');
        formData.append('message', text);

        input.value = ''; 
        fetch('chat_handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') loadMessages();
            });
    }

    document.getElementById("chat-message").addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendMessage();
        }
    });

    function checkUnreadCount() {
        if (!customerId || chatOpen) return;
        fetch(`chat_handler.php?action=get_unread_count&customer_id=${customerId}`)
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('unread-badge');
                if (data.unread > 0) {
                    badge.innerText = data.unread;
                    badge.style.display = 'flex';
                    
                    if (data.unread > previousUnreadCount) {
                        notificationSound.play().catch(error => console.log("Audio blocked by browser:", error)); 
                    }
                    
                    previousUnreadCount = data.unread;
                    
                } else {
                    badge.style.display = 'none';
                    previousUnreadCount = 0;
                }
            });
    }
    checkUnreadCount();
    setInterval(checkUnreadCount, 5000);
</script>
<link rel="stylesheet" href="fontawesome-free-7.1.0-web/css/all.css">
<script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
<?php include 'includes/footer.php'; ?>