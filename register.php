<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>

<div class="login-page">
    <div class="login-card">
        <h2>Register</h2>
        <p>Create an account for Aqua Prime</p>
        
        <form action="actions/register_action.php" method="POST">
            <div class="form-group">
                <label><i class="fa-solid fa-user" style="color: green"></i> Username</label>
                <input type="text" name="fullname" placeholder="Create Username" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-envelope" style="color: green"></i> Email Address</label>
                <input type="email" name="email" placeholder="Provide active Email Address" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-phone" style="color: green"></i> Phone Number</label>
                <input type="text" name="phone" placeholder="09xx xxx xxxx" required>
            </div>

            <div class="form-group">
                <label style="display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa-solid fa-map-location-dot" style="color: green;"></i> Delivery Address</span>
                    <button type="button" onclick="getLocation()" style="background: navy; color:white; border:none; padding:5px 10px; border-radius:5px; font-size:0.7rem; cursor:pointer;">
                        <i class="fa-solid fa-location-crosshairs"></i> Use GPS
                    </button>
                </label>
                <textarea id="addressField" name="address" placeholder="Click 'Use GPS' or type manually..." required style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; height: 80px;"></textarea>
                <small id="gps-status" style="color: #666; font-size: 0.75rem;"></small>
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-lock" style="color: green;"></i> Password</label>
                <input type="password" name="password" placeholder="Create a strong password" required>
            </div>
            
            <button type="submit" class="btn-login">CREATE ACCOUNT</button>
        </form>
        
        <div class="login-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<!-- AUTOMATIC GOOGLE MAP BASED LOCATION -->
<script>
function getLocation() {
    const status = document.getElementById("gps-status");
    const addressField = document.getElementById("addressField");

    if (navigator.geolocation) {
        status.textContent = "Locating...";
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else { 
        status.textContent = "Geolocation is not supported by this browser.";
    }
}

function showPosition(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const status = document.getElementById("gps-status");
    const addressField = document.getElementById("addressField");

    status.textContent = "Found coordinates! Fetching address...";

    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            addressField.value = data.display_name;
            status.textContent = "Address updated via GPS.";
            
        })
        .catch(error => {
            addressField.value = `Lat: ${lat}, Lng: ${lng} (San Carlos City)`;
            status.textContent = "Coordinates fetched!.";
        });
}

function showError(error) {
    const status = document.getElementById("gps-status");
    switch(error.code) {
        case error.PERMISSION_DENIED:
            status.textContent = "User denied the request for Geolocation.";
            break;
        case error.POSITION_UNAVAILABLE:
            status.textContent = "Location information is unavailable.";
            break;
        case error.TIMEOUT:
            status.textContent = "The request to get user location timed out.";
            break;
        case error.UNKNOWN_ERROR:
            status.textContent = "An unknown error occurred.";
            break;
    }
}
</script>

<link rel="stylesheet" href="fontawesome-free-7.1.0-web/css/all.css">
<script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
<?php include 'includes/footer.php'; ?>