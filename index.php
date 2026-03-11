<?php 
// Start the session to check who is visiting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the visitor is logged in as an admin, kick them to the dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

include 'includes/db.php'; 
include 'includes/header.php'; 
?>
<section class="hero">
    <h1>YOUR <span style="color: green;">PRIME</span> CHOICE</h1>
    <p>Stay hydrated with Aqua Prime's multi-stage purified water.</p>
    <a href="products.php" class="btn-main">
        <i class="fa-solid fa-cart-shopping"></i> ORDER NOW
    </a>
</section>

<div class="container" style="margin-top: 60px;">
    <h2 style="text-align: center; margin-bottom: 40px; color: var(--primary-blue); font-size: 2.5rem;">Why Choose Aqua Prime?</h2>
    <div class="grid">
        <div class="product-card">
            <h2><i class="fa-brands fa-envira" style="color: green;"></i> ECO-CONSCIOUS</h2>
            <p>"We use advanced filtration technology designed to minimize water waste. By choosing us, you’re supporting a sustainable process that provides premium hydration while protecting our local environment."</p>
        </div>
        <div class="product-card">
            <h2><i class="fa-solid fa-shield" style="color: green;"></i> VERIFIED SAFE</h2>
            <p>"Your health is our priority. Every drop undergoes a rigorous multi-stage purification process and regular laboratory testing to ensure it meets the highest standards for 100% purity and safety."</p>
        </div>
        <div class="product-card">
            <h2><i class="fa-solid fa-truck" style="color: green;"></i> FAST DELIVERY</h2>
            <p>"Skip the heavy lifting. Our dedicated local team provides swift, reliable doorstep delivery throughout San Carlos City, ensuring your home or office stays hydrated without the wait."</p>
        </div>
    </div>
</div>

<section class="location-section">
    <div class="map-container" style="width: 100%; height: 450px; overflow: hidden; border-radius: 10px;">
    <?php
    
    $is_online = @fsockopen("www.google.com", 80, $errno, $errstr, 2); 

    if ($is_online): 
        fclose($is_online);
    ?>
        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15692.714743076369!2d123.41415685628745!3d10.486582523074958!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1772992803787!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <?php else: ?>
        <div style="position: relative; width: 100%; height: 100%;">
            <img src="assets/img/map_offline.png" alt="Map Offline" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    <?php endif; ?>
</div>
        
        <div class="map-overlay-card">
            <h3><i class="fa-solid fa-location-dot"></i> Aqua Prime Station</h3>
            <p>San Carlos City, Negros Occidental, 6127</p>
            <p class="map-note">Open Daily: 8:00 AM - 5:00 PM</p>
        </div>
    </div>
</section>

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