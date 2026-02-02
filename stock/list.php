<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login check
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

$query = "SELECT * FROM stock ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock List</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .low-stock{
            background:#f8d7da !important;
            color:#721c24 !important;
            font-weight:bold;
        }
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">

    <div class="card">
        <h2 align="center">Stock List</h2>

        <center><a href="new.php" class="btn" style="margin-bottom:15px;">+ Add New Stock</a></center>

        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Item Name</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Total Stock</th>
                <th>Reorder Level</th>
                <th>Price</th>
                <!-- <th>Warranty (Months)</th> -->
                <th>Actions</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($result)) { 
                $lowStock = ($row['total_stock'] <= $row['min_quantity']);
            ?>
                <tr class="<?php echo $lowStock ? 'low-stock' : ''; ?>">
                    <td><?php echo $row['id']; ?></td>

                    <td>
                        <?php if($row['stock_image'] != "") { ?>
                            <img src="../uploads/<?php echo $row['stock_image']; ?>" width="50" height="50">
                        <?php } else { echo "No Image"; } ?>
                    </td>

                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['brand']; ?></td>
                    <td><?php echo $row['model']; ?></td>

                    <td><?php echo $row['total_stock']; ?></td>
                    <td><?php echo $row['min_quantity']; ?></td>

                    <td>
                        ₹ <?php echo number_format($row['selling_price'], 2); ?>
                    </td>

                    <!-- <td><?php echo $row['warranty_months']; ?></td> -->

                    <td>
                        <a class="btn" href="update.php?id=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>

</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
