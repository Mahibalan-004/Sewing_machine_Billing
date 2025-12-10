<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in redirect
if(!isset($_SESSION['user_id'])){
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h3 class="sidebar-title">Sewing Machine Billing</h3>

        <a href="../login/dashboard.php">Dashboard</a>

        <p class="sidebar-section">Jobcards</p>
        <a href="../jobcard/create.php">Create Jobcard</a>
        <a href="../jobcard/list.php">Jobcard List</a>

        <p class="sidebar-section">Sales</p>
        <a href="../sales/create.php">Create Sale</a>
        <a href="../sales/list.php">Sales List</a>

        <p class="sidebar-section">Stock</p>
        <a href="../stock/new.php">Add Stock</a>
        <a href="../stock/list.php">Stock List</a>

        <p class="sidebar-section">Outing</p>
        <a href="../outing/create.php">Create Outing</a>
        <a href="../outing/list.php">Outing List</a>

        <p class="sidebar-section">Employees</p>
        <a href="../master/new_employee.php">Add Employee</a>
        <a href="../master/list_employee.php">Employee List</a>

<a href="../login/logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- NAVBAR -->
    <div class="navbar">
        <span class="menu-btn" onclick="toggleSidebar()">☰</span>
        <h2>Sewing Machine Billing System</h2>
        <span class="nav-user">Logged in: <?php echo $_SESSION['username']; ?></span>
    </div>

</div>

<script>
function toggleSidebar(){
    var sb = document.getElementById("sidebar");
    sb.classList.toggle("sidebar-open");
}
</script>

</body>
</html>
