<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>

<div class="container">
    <h1 style="margin-bottom: 30px; color: var(--primary-blue);">Gallon and Bottled Water Products</h1>
    <div class="grid">
        <?php
        $res = $conn->query("SELECT * FROM products WHERE category NOT IN ('NonElectricDispenser', 'ElectricDispenser', '5Galloon', 'Stickers', 'Icecubes')");
        
        if($res->num_rows > 0) {
            while($item = $res->fetch_assoc()):
                $img_file = !empty($item['image_path']) ? $item['image_path'] : '5pcs.png';
        ?>
        <div class="product-card">
            <img src="assets/img/<?php echo $img_file; ?>" alt="<?php echo $item['name']; ?>" style="width: 200px; height: auto;">
            <h3 style="margin-top: 15px;"><?php echo $item['name']; ?></h3>
            <p style="color: #666;"><?php echo $item['description']; ?></p>
            <div class="price" style="color: var(--aqua-green); font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                ₱ <?php echo number_format($item['price'], 2); ?>
            </div>

            <form action="actions/add_to_order.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                
                <div class="qty-wrapper">
                    <button type="button" class="qty-btn" onclick="updateQty(this, -1)">-</button>
                    <input type="number" name="quantity" value="1" class="qty-input" min="1" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                </div>

                <button type="submit" class="btn-main" style="width: 100%; border: none; cursor: pointer;">ADD TO ORDER</button>
            </form>
        </div>
        <?php endwhile; } else { echo "<p>No water products found.</p>"; } ?>
    </div>
</div>

<div class="container">
    <h1 style="margin-bottom: 30px; color: var(--primary-blue);">Dispenser and Other Products</h1>
    <div class="grid">
        <?php
        $res = $conn->query("SELECT * FROM products WHERE category IN ('NonElectricDispenser', 'ElectricDispenser', '5Galloon', 'Stickers', 'Icecubes')");
        
        if($res->num_rows > 0) {
            while($item = $res->fetch_assoc()):
                $img_file = !empty($item['image_path']) ? $item['image_path'] : 'default.png';
        ?>
        <div class="product-card">
            <img src="assets/img/<?php echo $img_file; ?>" alt="<?php echo $item['name']; ?>" style="width: 200px; height: auto;">
            <h3 style="margin-top: 15px;"><?php echo $item['name']; ?></h3>
            <p style="color: #666;"><?php echo $item['description']; ?></p>
            <div class="price" style="color: var(--aqua-green); font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                ₱ <?php echo number_format($item['price'], 2); ?>
            </div>

            <form action="actions/add_to_order.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                
                <div class="qty-wrapper">
                    <button type="button" class="qty-btn" onclick="updateQty(this, -1)">-</button>
                    <input type="number" name="quantity" value="1" class="qty-input" min="1" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                </div>

                <button type="submit" class="btn-main" style="width: 100%; border: none; cursor: pointer;">ADD TO ORDER</button>
            </form>
        </div>
        <?php endwhile; } else { echo "<p style='color: #888;'>New products coming soon to San Carlos City!</p>"; } ?>
    </div>
</div>

<div id="cart-success-modal" class="success-modal">
    <div class="check-icon-animate">
        <i class="fa-solid fa-check"></i>
    </div>
    <h3 style="color: var(--primary-blue); margin-bottom: 5px;">Success!</h3>
    <p style="color: #666; font-size: 0.9rem;">Item successfully added to cart</p>
</div>

<script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>

<!-- Quantity Order -->
<script>
function updateQty(btn, change) {
    const container = btn.parentElement;
    const input = container.querySelector('.qty-input');
    let newValue = parseInt(input.value) + change;
    if (newValue < 1) newValue = 1;
    input.value = newValue;
}

// AJAX Add to Order Logic
document.querySelectorAll('form[action="actions/add_to_order.php"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); 
        const formData = new FormData(this);

        fetch('actions/add_to_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSuccessModal();
            } else if (data.status === 'redirect') {
                alert(data.message);
                window.location.href = 'login.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function showSuccessModal() {
    const modal = document.getElementById('cart-success-modal');
    modal.classList.add('active');
    setTimeout(() => {
        modal.classList.remove('active');
    }, 2000);
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