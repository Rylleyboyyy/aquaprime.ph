<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>

<div class="login-page">
    <div class="login-card">
        <h2>Log In</h2>
        <p>Login to your Aqua Prime account</p>
        
        <form action="actions/login_action.php" method="POST">
            <div class="form-group">
                <label><i class="fa-solid fa-circle-user" style="color: green;"></i> Username</label>
                <input type="text" name="fullname" placeholder="Enter your Username" required>
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-lock" style="color: green;"></i> Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn-login">LOGIN</button>
        </form>
        
        <div class="login-footer">
            New to Aqua Prime? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<link rel="stylesheet" href="fontawesome-free-7.1.0-web/css/all.css">
<script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
<?php include 'includes/footer.php'; ?>