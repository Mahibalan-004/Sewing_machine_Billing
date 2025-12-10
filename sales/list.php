<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Require login
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Fetch all sales orders
$query = "
    SELECT s.*, 
    (SELECT COUNT(*) FROM sales_items WHERE sale_id = s.id) AS item_count
    FROM sales s ORDER BY s.id DESC
";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales List</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .paid { color: green; font-weight: bold; }
        .pending { color: red; font-weight: bold; }
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">

        <h2>Sales Order List</h2>

        <a href="create.php" class="btn" style="margin-bottom:15px;">+ Create New Sales Order</a>

        <table>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>City</th>
                <th>Items</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Actions</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($result)) { 
                $bal = floatval($row['balance']);
            ?>
            <tr>
                <td><?php echo $row['id']; ?></td>

                <td>
                    <?php echo ($row['order_status']=="Invoice") 
                        ? "<span class='paid'>Invoice</span>" 
                        : "New"; ?>
                </td>

                <td><?php echo $row['order_date']; ?></td>

                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['customer_phone']; ?></td>
                <td><?php echo $row['city']; ?></td>

                <td><?php echo $row['item_count']; ?></td>

                <td>₹ <?php echo number_format($row['total_amount'], 2); ?></td>
                <td>₹ <?php echo number_format($row['paid_amount'], 2); ?></td>

                <td>
                    <?php 
                        if($bal > 0) 
                            echo "<span class='pending'>₹ ".number_format($bal,2)."</span>";
                        else
                            echo "<span class='paid'>₹ 0.00</span>";
                    ?>
                </td>

                <td>
                    <a class="btn" href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                </td>
            </tr>
            <?php } ?>

        </table>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
