<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login check
if (!isset($_SESSION['user_id'])) {
    redirect("../login/login.php");
}

// Search
$search = "";
$where  = "";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE purchase_no LIKE '%$search%' 
              OR supplier_phone LIKE '%$search%' 
              OR supplier_name LIKE '%$search%'";
}

// Group purchases (IMPORTANT)
$query = "
    SELECT 
        purchase_no,
        order_date,
        supplier_name,
        supplier_phone,
        SUM(total_price) AS total_amount,
        paid_amount
    FROM purchases
    $where
    GROUP BY purchase_no
    ORDER BY order_date DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase List</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<?php include("../includes/header.php"); ?>

<div class="container">
<div class="card">

    <h2>Purchase List</h2>

    <!-- SEARCH -->
    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search"
               placeholder="Search Purchase No / Supplier Phone"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn">Search</button>
        <a href="list.php" class="btn" style="background:#7f8c8d">Clear</a>
    </form>

    <a href="create.php" class="btn" style="margin-bottom:15px;">+ New Purchase</a>

    <table>
        <tr>
            <th>#</th>
            <th>Purchase No</th>
            <th>Order Date</th>
            <th>Supplier</th>
            <th>Phone</th>
            <th>Total Amount</th>
            <th>Paid</th>
        </tr>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['purchase_no']) ?></td>
                    <td><?= date("d-m-Y", strtotime($row['order_date'])) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_phone']) ?></td>
                    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                    <td>₹<?= number_format($row['paid_amount'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;color:#999;">
                    No Purchases Found
                </td>
            </tr>
        <?php endif; ?>

    </table>

</div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
