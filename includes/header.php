<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aqua Prime | San Carlos City</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="fontawesome-free-7.1.0-web/css/all.css">
    <script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
</head>
<body>

<div id="loader-wrapper">
    <div class="loader-container">
        <img src="assets/img/logo.png" class="logo-display logo-bg" alt="Aqua Prime">
        <div class="logo-fill-wrapper">
            <img src="assets/img/logo.png" class="logo-display logo-fg" alt="Aqua Prime">
        </div>
        <div class="loading-info">
            <p>Refreshing...</p>
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>
</div>

<nav>
    <div class="logo">
        <a href="index.php"><img src="assets/img/logo.png" alt="Aqua Prime"></a>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="user-name-left-icon">
            <i class="fa-solid fa-circle-user"></i>
        </div>
        <div class="user-name-left">
            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
        </div>
    <?php endif; ?>

    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-house"></i> HOME</a></li>
        <li><a href="products.php"><i class="fa-solid fa-droplet"></i> PRODUCTS</a></li>
        <li><a href="contact.php"><i class="fa-solid fa-address-book"></i> CONTACT US</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="my_orders.php"><i class="fa-solid fa-cart-shopping"></i> MY CART</a></li>
            <li><a href="actions/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> LOGOUT</a></li>
        <?php else: ?>
            <li><a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> LOGIN / REGISTER</a></li>
        <?php endif; ?>
    </ul>
</nav>