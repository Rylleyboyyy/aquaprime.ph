<?php 
session_start();
include '../includes/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Filename sa PDF REPORT
$date_filename = "AquaPrime-Report-" . date("F_d_Y");

// TOTAL INCOME SA ADMIN DASHBOARD
$sales_query = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Completed' AND admin_hidden = 0");
$total_sales = $sales_query->fetch_assoc()['total'] ?? 0;

$orders_sql = "SELECT o.*, u.fullname 
               FROM orders o 
               JOIN users u ON o.user_id = u.id 
               WHERE o.status = 'Completed' 
               AND o.admin_hidden = 0 
               ORDER BY o.order_date DESC";
$orders_res = $conn->query($orders_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $date_filename; ?></title>
    <link rel="stylesheet" href="../assets/css/adminstyle.css">
    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.css">
    <script src="https://kit.fontawesome.com/2efbf477ad.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="no-print" style="text-align: right; margin-right: 350px">
    <button onclick="window.print()" class="print-btn">
    <i class="fa-solid fa-file-pdf"></i> SAVE AS PDF
</button>
</div>

<div class="container">
    <div class="header">
        <img src="../assets/img/logo.png" class="logo">
        <div class="business-name">AQUA PRIME WATER REFILLING STATION</div>
        <p style="margin: 5px 0; color: #666;">Official Sales Revenue Report</p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th style="text-align: right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $orders_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo date("M d, Y | h:i A", strtotime($row['order_date'])); ?></td>
                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                <td><strong><?php echo $row['status']; ?></strong></td>
                <td style="text-align: right; font-weight: bold;">
                    ₱ <?php echo number_format($row['total_amount'], 2); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-banner">
        TOTAL REVENUE: ₱ <?php echo number_format($total_sales, 2); ?>
    </div>

    <div style="margin-top: 60px; text-align: center; font-size: 12px; color: #999;">
        <p>© <?php echo date("Y"); ?> Aqua Prime Philippines</p>
    </div>
</div>

</body>
</html>