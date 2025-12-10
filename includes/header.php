<?php
if(!isset($_SESSION)) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login/login.php");
    exit();
}
?>
<header>
    <nav>
        <div style="color:#ecf0f1;font-size:20px;font-weight:bold;">
            Sewing Machines Billing
        </div>
        <div>
            <a href="../login/dashboard.php">Dashboard</a>
            <a href="../jobcard/list.php">Jobcard</a>
            <a href="../sales/list.php">Sales</a>
            <a href="../stock/list.php">Stock</a>
            <a href="../outing/list.php">Outing</a>
            <a href="../master/list_employee.php">Employees</a>
            <a href="../login/logout.php" style="color:#e74c3c;">Logout</a>
        </div>
    </nav>
</header>
