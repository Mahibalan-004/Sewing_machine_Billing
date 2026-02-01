<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

if(!isset($_SESSION['user_id'])){
    redirect("login/login.php");
}

// SALES TODAY
$sales_today_q = mysqli_query($conn,
    "SELECT SUM(total_amount) AS total FROM sales WHERE sales_date = CURDATE()"
);
$sales_today = mysqli_fetch_assoc($sales_today_q)['total'];
if($sales_today == "") $sales_today = 0;

// TOTAL STOCK ITEMS
$stock_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock");
$total_stock_items = mysqli_fetch_assoc($stock_q)['total'];

// LOW STOCK ITEMS
$low_stock_q = mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM stock WHERE total_stock <= min_quantity"
);
$low_stock = mysqli_fetch_assoc($low_stock_q)['total'];

// PENDING OUTINGS (expected_return date not empty and still pending)
$pending_outings_q = mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM outing WHERE expected_return IS NOT NULL AND expected_return != '' AND expected_return >= CURDATE()"
);
$pending_outings = mysqli_fetch_assoc($pending_outings_q)['total'];

// TOTAL JOBCARDS
$jobcard_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jobcards");
$total_jobcards = mysqli_fetch_assoc($jobcard_q)['total'];

// TOTAL EMPLOYEES
$employee_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM employees");
$total_employees = mysqli_fetch_assoc($employee_q)['total'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard { display:flex; flex-wrap:wrap; gap:20px; }
        .card-box {
            flex:1; min-width:250px; background:white; padding:20px;
            border-radius:10px; box-shadow:0 0 8px rgba(0,0,0,0.1);
            text-align:center; transition:0.3s;
        }
        .card-box:hover { transform:scale(1.05); }
        .card-title { font-size:18px; margin-bottom:10px; }
        .card-value { font-size:28px; font-weight:bold; color:#2c3e50; }
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <h2>Dashboard Overview</h2>

    <div class="dashboard">

        <div class="card-box">
            <div class="card-title">Sales Today</div>
            <div class="card-value">₹ <?php echo number_format($sales_today, 2); ?></div>
        </div>

        <div class="card-box">
            <div class="card-title">Total Stock Items</div>
            <div class="card-value"><?php echo $total_stock_items; ?></div>
        </div>

        <div class="card-box">
            <div class="card-title">Low Stock Alerts</div>
            <div class="card-value"><?php echo $low_stock; ?></div>
        </div>

        <div class="card-box">
            <div class="card-title">Pending Outings</div>
            <div class="card-value"><?php echo $pending_outings; ?></div>
        </div>

        <div class="card-box">
            <div class="card-title">Total Jobcards</div>
            <div class="card-value"><?php echo $total_jobcards; ?></div>
        </div>

        <div class="card-box">
            <div class="card-title">Total Employees</div>
            <div class="card-value"><?php echo $total_employees; ?></div>
        </div>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
