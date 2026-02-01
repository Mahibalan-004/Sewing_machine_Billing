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

$sale_id = intval($_GET['id']);

/* FETCH SALE */
$orderQ = mysqli_query($conn, "SELECT * FROM sales WHERE id=$sale_id");
$order = mysqli_fetch_assoc($orderQ);

/* FETCH ITEMS */
$itemQ = mysqli_query($conn, "SELECT si.*, st.id AS stock_id 
    FROM sales_items si
    LEFT JOIN stock st ON si.item_name = st.item_name
    WHERE si.sale_id=$sale_id");

/* FETCH STOCK */
$stockQ = mysqli_query($conn, "SELECT * FROM stock ORDER BY item_name");

$success = $error = "";

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $order_status = clean($_POST['order_status']);
    $sales_date   = clean($_POST['sales_date']);
    $total_amount = floatval($_POST['total_amount']);
    $paid_amount  = floatval($_POST['paid_amount']);
    $balance      = floatval($_POST['balance']);

    mysqli_query($conn, "
        UPDATE sales SET
            order_status='$order_status',
            sales_date='$sales_date',
            total_amount='$total_amount',
            paid_amount='$paid_amount',
            balance='$balance'
        WHERE id=$sale_id
    ");

    mysqli_query($conn, "DELETE FROM sales_items WHERE sale_id=$sale_id");

    foreach ($_POST['stock_id'] as $i => $sid) {

        if (!$sid) continue;

        $qty   = floatval($_POST['qty'][$i]);
        $price = floatval($_POST['price'][$i]);
        $tpr   = floatval($_POST['tprice'][$i]);

        mysqli_query($conn, "
            INSERT INTO sales_items
            (sale_id, item_name, qty, price_per_qty, total_price)
            SELECT
                '$sale_id', item_name, '$qty', '$price', '$tpr'
            FROM stock WHERE id='$sid'
        ");
    }

$_SESSION['success'] = "Sales updated successfully!";
    header("Location:list.php");
    exit;}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Sales</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 8px
        }

        .add-btn {
            color: #2980b9;
            font-weight: bold;
            cursor: pointer
        }

        .remove {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px
        }

        .readonly {
            background: #eee
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2>Edit Sales</h2>
            <?= $success ? "<p style='color:green'>$success</p>" : "" ?>

            <form method="POST">

                <h3>Order</h3>
                <div class="grid">
                    <select name="order_status">
                        <option <?= $order['order_status'] == "New" ? "selected" : "" ?>>New</option>
                        <option <?= $order['order_status'] == "Invoice" ? "selected" : "" ?>>Invoice</option>
                    </select>
                    <input type="date" name="sales_date" value="<?= $order['sales_date'] ?>">
                </div>

                <h3>Customer (Readonly)</h3>
                <div class="grid">
                    <input class="readonly" value="<?= $order['customer_phone'] ?>" readonly>
                    <input class="readonly" value="<?= $order['customer_name'] ?>" readonly>
                    <input class="readonly" value="<?= $order['city'] ?>" readonly>
                </div>

                <h3>Items</h3>
                <div id="items">

                    <?php while ($it = mysqli_fetch_assoc($itemQ)) { ?>
                        <div class="item-row">
                            <select name="stock_id[]" onchange="setPrice(this)">
                                <option value="">Item</option>
                                <?php mysqli_data_seek($stockQ, 0);
                                while ($s = mysqli_fetch_assoc($stockQ)) { ?>
                                    <option value="<?= $s['id'] ?>" data-price="<?= $s['selling_price'] ?>"
                                        <?= $s['item_name'] == $it['item_name'] ? "selected" : "" ?>>
                                        <?= $s['item_name'] ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <input name="qty[]" value="<?= $it['qty'] ?>" onkeyup="calcRow(this)">
                            <input name="price[]" value="<?= $it['price_per_qty'] ?>" onkeyup="calcRow(this)">
                            <input name="tprice[]" class="row-total" value="<?= $it['total_price'] ?>" readonly>
                            <button type="button" class="remove" onclick="this.parentNode.remove();calcTotal()">X</button>
                        </div>
                    <?php } ?>

                </div>

                <span class="add-btn" onclick="addItem()">+ Add Item</span>

                <h3>Payment</h3>
                <div class="grid">
                    <input id="total_amount" name="total_amount" value="<?= $order['total_amount'] ?>" readonly>
                    <input id="paid_amount" name="paid_amount" value="<?= $order['paid_amount'] ?>" onkeyup="calcBalance()">
                    <input id="balance" name="balance" value="<?= $order['balance'] ?>" readonly>
                </div>

                <br>
                <button class="btn">Update Sales</button>
                <a href="list.php" class="btn" style="background:#7f8c8d">Back</a>

            </form>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>

    <script>
        function setPrice(sel) {
            let p = sel.options[sel.selectedIndex].dataset.price || 0;
            sel.parentNode.children[2].value = p;
            calcRow(sel);
        }

        function calcRow(el) {
            let r = el.parentNode;
            let q = +r.children[1].value || 0;
            let p = +r.children[2].value || 0;
            r.children[3].value = (q * p).toFixed(2);
            calcTotal();
        }

        function calcTotal() {
            let t = 0;
            document.querySelectorAll(".row-total").forEach(i => t += +i.value || 0);
            total_amount.value = t.toFixed(2);
            calcBalance();
        }

        function calcBalance() {
            balance.value = (total_amount.value - paid_amount.value).toFixed(2);
        }

        function addItem() {
            let d = document.createElement('div');
            d.className = "item-row";
            d.innerHTML = `
<select name="stock_id[]" onchange="setPrice(this)">
<option value="">Item</option>
<?php mysqli_data_seek($stockQ, 0);
while ($s = mysqli_fetch_assoc($stockQ)) { ?>
<option value="<?= $s['id'] ?>" data-price="<?= $s['selling_price'] ?>"><?= $s['item_name'] ?></option>
<?php } ?>
</select>
<input name="qty[]" value="1" onkeyup="calcRow(this)">
<input name="price[]" onkeyup="calcRow(this)">
<input name="tprice[]" class="row-total" readonly>
<button type="button" class="remove" onclick="this.parentNode.remove();calcTotal()">X</button>`;
            items.appendChild(d);
        }
    </script>

</body>

</html>