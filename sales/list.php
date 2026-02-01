<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Uncomment when login system is ready
// if (!isset($_SESSION['user_id'])) {
//     redirect("../login/login.php");
// }

// Fetch all sales orders
$query = "
    SELECT s.*, 
    (SELECT COUNT(*) FROM sales_items WHERE sale_id = s.id) AS item_count
    FROM sales s 
    ORDER BY s.id DESC
";

$result = mysqli_query($conn, $query);

// Safety check
if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales List</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .paid {
            color: green;
            font-weight: bold;
        }

        .pending {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2>Sales Order List</h2>

            <a href="create.php" class="btn" style="margin-bottom:15px;">
                + Create New Sales Order
            </a>

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

                <?php if (mysqli_num_rows($result) > 0) { ?>

                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        $bal = (float)$row['balance'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>

                            <td>
                                <?= ($row['order_status'] === "Invoice")
                                    ? "<span class='paid'>Invoice</span>"
                                    : "New"; ?>
                            </td>

                            <td><?= htmlspecialchars($row['sales_date']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($row['city']) ?></td>

                            <td><?= htmlspecialchars($row['item_count']) ?></td>

                            <td>₹ <?= number_format($row['total_amount'], 2) ?></td>
                            <td>₹ <?= number_format($row['paid_amount'], 2) ?></td>

                            <td>
                                <?php
                                if ($bal > 0) {
                                    echo "<span class='pending'>₹ " . number_format($bal, 2) . "</span>";
                                } else {
                                    echo "<span class='paid'>₹ 0.00</span>";
                                }
                                ?>
                            </td>

                            <td>
                                <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php } ?>

                <?php } else { ?>
                    <tr>
                        <td colspan="11" style="text-align:center;">
                            No sales orders found.
                        </td>
                    </tr>
                <?php } ?>

            </table>

        </div>
    </div>

    <?php include("../includes/footer.php"); ?>

</body>

</html>