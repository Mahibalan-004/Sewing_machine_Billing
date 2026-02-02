<?php
session_start();
require_once("../config/db.php");

// Auto Purchase No
$pno = "PO-" . date("ymd") . "-" . rand(100, 999);
$order_date = date("Y-m-d");

// Suppliers
$supRes = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // SUPPLIER
    if ($_POST['supplier_type'] == "new") {
        mysqli_query($conn, "
            INSERT INTO suppliers
            (phone,name,email,addr1,addr2,city,pincode)
            VALUES
            (
                '{$_POST['phone']}',
                '{$_POST['name']}',
                '{$_POST['email']}',
                '{$_POST['addr1']}',
                '{$_POST['addr2']}',
                '{$_POST['city']}',
                '{$_POST['pincode']}'
            )
        ");
        $supplier_id = mysqli_insert_id($conn);
        $supplier_name = $_POST['name'];
        $supplier_phone = $_POST['phone'];
    } else {
        $supplier_id = $_POST['supplier_id'];
        $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM suppliers WHERE id=$supplier_id"));
        $supplier_name = $s['name'];
        $supplier_phone = $s['phone'];
    }

    foreach ($_POST['item_name'] as $i => $name) {

        if ($name == "") continue;

        $qty = $_POST['qty'][$i];
        $pprice = $_POST['purchase_price'][$i];
        $sprice = $_POST['selling_price'][$i];
        $total = $qty * $pprice;

        mysqli_query($conn, "
            INSERT INTO purchases
            (purchase_no,order_date,supplier_id,supplier_name,supplier_phone,
             item_name,brand,model,qty,purchase_price,selling_price,total_price,
             payment_date,payment_mode,reference_no,paid_amount)
            VALUES
            (
                '$pno',
                '$order_date',
                '$supplier_id',
                '$supplier_name',
                '$supplier_phone',
                '$name',
                '{$_POST['brand'][$i]}',
                '{$_POST['model'][$i]}',
                '$qty',
                '$pprice',
                '$sprice',
                '$total',
                '{$_POST['payment_date']}',
                '{$_POST['payment_mode']}',
                '{$_POST['reference_no']}',
                '{$_POST['paid_amount']}'
            )
        ");

        // STOCK UPDATE
        $chk = mysqli_query($conn, "
            SELECT id FROM stock
            WHERE item_name='$name'
            AND brand='{$_POST['brand'][$i]}'
            AND model='{$_POST['model'][$i]}'
        ");

        if (mysqli_num_rows($chk)) {
            $r = mysqli_fetch_assoc($chk);
            mysqli_query($conn, "
                UPDATE stock
                SET total_stock = total_stock + $qty,
                    purchase_price = '$pprice',
                    selling_price = '$sprice'
                WHERE id={$r['id']}
            ");
        } else {
            mysqli_query($conn, "
                INSERT INTO stock
                (item_name,brand,model,total_stock,purchase_price,selling_price)
                VALUES
                ('$name','{$_POST['brand'][$i]}','{$_POST['model'][$i]}',
                 '$qty','$pprice','$sprice')
            ");
        }
    }

    header("Location:list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Purchase</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px
        }

        .section-title {
            background: #f5f7fa;
            padding: 10px 15px;
            border-left: 16px solid #3498db;
            border-radius: 6px;
        }

        .section-title:hover {
            color: #3498db;
            transition: 0.3s ease;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 1.2fr 1.2fr 40px;
            gap: 8px;
            margin-bottom: 6px;
        }

        .del-btn {
            background: #d9534f;
            color: #fff;
            border: none;
            padding: 6px
        }
    </style>

    <script>
        function supplierToggle(val) {
            document.getElementById("supplierSelect").disabled = val === "new";
            document.querySelectorAll(".sup-input").forEach(i => {
                i.disabled = val !== "new";
                if (val !== "new") i.value = "";
            });
        }

        function calcRow(el) {
            let row = el.closest(".item-row");

            let qty = parseFloat(row.querySelector("input[name='qty[]']").value) || 0;
            let price = parseFloat(row.querySelector("input[name='purchase_price[]']").value) || 0;

            row.querySelector("input[name='total_price[]']").value =
                (qty * price).toFixed(2);
        }

        function addRow() {
            let row = document.querySelector(".item-row").cloneNode(true);

            row.querySelectorAll("input").forEach(i => i.value = "");

            document.getElementById("items").appendChild(row);
        }

        function removeRow(btn) {
            let rows = document.querySelectorAll(".item-row");
            if (rows.length > 1) {
                btn.closest(".item-row").remove();
            } else {
                alert("At least one item is required");
            }
        }
    </script>

</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2 align="center">Create Purchase</h2>

            <form method="POST">

                <h3 class="section-title">Purchase Info</h3>
                <div class="grid-2">
                    <div>
                        <label>Purchase No</label>
                        <input value="<?= $pno ?>" readonly>
                    </div>
                    <div>
                        <label>Order Date</label>
                        <input value="<?= $order_date ?>" readonly>
                    </div>
                </div><br>

                <h3 class="section-title">Supplier</h3>
                <div class="grid-2">
                    <select name="supplier_type" onchange="supplierToggle(this.value)">
                        <option value="existing">Existing Supplier</option>
                        <option value="new">New Supplier</option>
                    </select>

                    <select name="supplier_id" id="supplierSelect">
                        <?php while ($s = mysqli_fetch_assoc($supRes)): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['name'] ?> (<?= $s['phone'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid-3">
                    <input class="sup-input" name="phone" placeholder="Phone" disabled>
                    <input class="sup-input" name="name" placeholder="Name" disabled>
                    <input class="sup-input" name="email" placeholder="Email" disabled>
                    <input class="sup-input" name="addr1" placeholder="Address 1" disabled>
                    <input class="sup-input" name="addr2" placeholder="Address 2" disabled>
                    <input class="sup-input" name="city" placeholder="City" disabled>
                    <input class="sup-input" name="pincode" placeholder="Pincode" disabled>
                </div><br>

                <h3 class="section-title">Items</h3>

                <div id="items">
                    <div class="item-row">
                        <input name="item_name[]" placeholder="Item Name" required>

                        <input name="brand[]" placeholder="Brand">

                        <input name="model[]" placeholder="Model">

                        <input type="number" name="qty[]" placeholder="Qty"
                            min="1" oninput="calcRow(this)" required>

                        <input type="number" step="0.01" name="purchase_price[]"
                            placeholder="Purchase Price" oninput="calcRow(this)" required>

                        <input type="number" step="0.01" name="selling_price[]"
                            placeholder="Selling Price">

                        <input type="number" step="0.01" name="total_price[]"
                            placeholder="Total" readonly>

                        <button type="button" class="del-btn" onclick="removeRow(this)">❌</button>
                    </div>
                </div>

                <button type="button" onclick="addRow()">+ Add Item</button><br><br>

                <h3 class="section-title">Payment</h3>
                <div class="grid-4">
                    <input type="date" name="payment_date">
                    <input name="payment_mode" placeholder="Mode">
                    <input name="reference_no" placeholder="Ref No">
                    <input type="number" step="0.01" name="paid_amount" placeholder="Amount">
                </div>

                <br>
                <center><button class="btn">Submit Purchase</button>
                <button type="reset" class="btn">Clear</button></center>

            </form>

        </div>
    </div>


    <?php include("../includes/footer.php"); ?>
</body>

</html>