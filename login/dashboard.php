<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// User must be logged in
if(!isset($_SESSION['user_id'])){
    redirect("login.php");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Select a module to continue:</p>

        <div style="margin-top:20px;">
            <a class="btn" href="../jobcard/list.php">Jobcard</a>
            <a class="btn" href="../sales/list.php">Sales</a>
            <a class="btn" href="../stock/list.php">Stock</a>
            <a class="btn" href="../outing/list.php">Outing</a>
            <a class="btn" href="../master/list_employee.php">Employee Master</a>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
