<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

if (!isset($_SESSION['user_id'])) {
    redirect("../login/login.php");
}

if (!isset($_GET['id'])) {
    redirect("list.php");
}

$id = intval($_GET['id']);

/* ================= FETCH DATA ================= */

$jobRes = mysqli_query($conn, "SELECT * FROM jobcards WHERE id=$id");
$data   = mysqli_fetch_assoc($jobRes);

if (!$data) {
    redirect("list.php");
}

$stockRes = mysqli_query($conn, "SELECT * FROM stock ORDER BY item_name");
$itemRes  = mysqli_query($conn, "SELECT * FROM jobcard_items WHERE jobcard_id=$id");
$labRes   = mysqli_query($conn, "SELECT * FROM jobcard_labour WHERE jobcard_id=$id");

/* ================= TOTALS ================= */

$spareTotal = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(total_amount) AS t FROM jobcard_items WHERE jobcard_id=$id"
))['t'] ?? 0;

$labourTotal = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(labour_cost) AS t FROM jobcard_labour WHERE jobcard_id=$id"
))['t'] ?? 0;

$grandTotal = $spareTotal + $labourTotal;

/* ================= UPDATE JOB STATUS & PAYMENT ================= */

if (isset($_POST['update_jobcard'])) {

    $job_status  = clean($_POST['job_status']);
    $paid_amount = floatval($_POST['paid_amount']);

    mysqli_query($conn, "
        UPDATE jobcards SET
            job_status='$job_status',
            paid_amount='$paid_amount'
        WHERE id=$id
    ");

    redirect("edit.php?id=$id");
}

/* ================= ADD SPARE ================= */

if (isset($_POST['add_spare'])) {

    $stock_id = intval($_POST['stock_id']);
    $qty      = intval($_POST['qty']);

    $s = mysqli_query($conn, "SELECT * FROM stock WHERE id=$stock_id");
    $stock = mysqli_fetch_assoc($s);

    if ($qty > 0 && $qty <= $stock['total_stock']) {

        $price = $stock['selling_price'];
        $total = $price * $qty;

        mysqli_query($conn, "
            INSERT INTO jobcard_items
            (jobcard_id, jobcard_no, stock_id, item_name, qty, price, total_amount)
            VALUES
            ('$id','{$data['jobcard_no']}','$stock_id',
             '{$stock['item_name']}','$qty','$price','$total')
        ");

        mysqli_query($conn, "
            UPDATE stock
            SET total_stock = total_stock - $qty
            WHERE id=$stock_id
        ");
    }

    redirect("edit.php?id=$id");
}

/* ================= DELETE SPARE ================= */

if (isset($_GET['del_spare'])) {

    $sid = intval($_GET['del_spare']);

    $r = mysqli_query($conn, "SELECT * FROM jobcard_items WHERE id=$sid");
    $sp = mysqli_fetch_assoc($r);

    mysqli_query($conn, "
        UPDATE stock
        SET total_stock = total_stock + {$sp['qty']}
        WHERE id={$sp['stock_id']}
    ");

    mysqli_query($conn, "DELETE FROM jobcard_items WHERE id=$sid");

    redirect("edit.php?id=$id");
}

/* ================= ADD LABOUR ================= */

if (isset($_POST['add_labour'])) {

    $labour_name = clean($_POST['labour_name']);
    $labour_cost = floatval($_POST['labour_cost']);

    mysqli_query($conn, "
        INSERT INTO jobcard_labour
        (jobcard_id, jobcard_no, labour_name, labour_cost)
        VALUES
        ('$id','{$data['jobcard_no']}','$labour_name','$labour_cost')
    ");

    redirect("edit.php?id=$id");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Jobcard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px
        }

        .readonly {
            background: #f3f3f3
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2>Edit Jobcard</h2>

            <!-- ================= JOB INFO ================= -->
            <h3>Jobcard Info</h3>
            <div class="grid-2">
                <input value="<?= $data['jobcard_no'] ?>" readonly class="readonly">
                <input value="<?= $data['jobcard_date'] ?>" readonly class="readonly">
            </div>

            <!-- ================= CUSTOMER ================= -->
            <h3>Customer Info</h3>
            <div class="grid">
                <input value="<?= $data['customer_phone'] ?>" readonly class="readonly">
                <input value="<?= $data['customer_name'] ?>" readonly class="readonly">
                <input value="<?= $data['customer_city'] ?>" readonly class="readonly">
            </div>

            <!-- ================= MACHINE ================= -->
            <h3>Machine & Work</h3>
            <div class="grid">
                <input value="<?= $data['machine_name'] ?>" readonly class="readonly">
                <input value="<?= $data['serial_number'] ?>" readonly class="readonly">
                <input value="<?= $data['work_type'] ?>" readonly class="readonly">
            </div>

            <textarea readonly class="readonly"><?= $data['remarks'] ?></textarea>

            <hr>

            <!-- ================= STATUS & PAYMENT ================= -->
            <form method="POST">

                <h3>Job Status</h3>
                <select name="job_status" required>
                    <option <?= $data['job_status'] == "New Job" ? "selected" : "" ?>>New Job</option>
                    <option <?= $data['job_status'] == "In Progress" ? "selected" : "" ?>>In Progress</option>
                    <option <?= $data['job_status'] == "Completed" ? "selected" : "" ?>>Completed</option>
                    <option <?= $data['job_status'] == "Delivered" ? "selected" : "" ?>>Delivered</option>
                </select>

                <h3>Payment</h3>
                <input type="number" step="0.01" name="paid_amount"
                    value="<?= $data['paid_amount'] ?>" required>

                <h3>Grand Total</h3>
                <input value="₹<?= number_format($grandTotal, 2) ?>" readonly class="readonly">

                <br><br>
                <button class="btn" name="update_jobcard">Update Jobcard</button>

            </form>

            <hr>

            <!-- ================= SPARES ================= -->
            <h3>Spares</h3>
            <form method="POST" class="grid">
                <select name="stock_id" required>
                    <option value="">Select Spare</option>
                    <?php while ($s = mysqli_fetch_assoc($stockRes)): ?>
                        <option value="<?= $s['id'] ?>"><?= $s['item_name'] ?> (<?= $s['total_stock'] ?>)</option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="qty" min="1" required>
                <button class="btn" name="add_spare">Add Spare</button>
            </form>

            <table>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                <?php while ($i = mysqli_fetch_assoc($itemRes)): ?>
                    <tr>
                        <td><?= $i['item_name'] ?></td>
                        <td><?= $i['qty'] ?></td>
                        <td><?= number_format($i['price'], 2) ?></td>
                        <td><?= number_format($i['total_amount'], 2) ?></td>
                        <td><a class="btn" href="?id=<?= $id ?>&del_spare=<?= $i['id'] ?>">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <hr>

            <!-- ================= LABOUR ================= -->
            <h3>Labour</h3>
            <form method="POST" class="grid">
                <input name="labour_name" required>
                <input type="number" step="0.01" name="labour_cost" required>
                <button class="btn" name="add_labour">Add Labour</button>
            </form>

            <table>
                <tr>
                    <th>Work</th>
                    <th>Cost</th>
                </tr>
                <?php while ($l = mysqli_fetch_assoc($labRes)): ?>
                    <tr>
                        <td><?= $l['labour_name'] ?></td>
                        <td><?= number_format($l['labour_cost'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <br>
            <a href="list.php" class="btn" style="background:#7f8c8d">Back</a>

        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>