<?php
session_start();
include "../config/db.php";
$success = $_SESSION['sales_success'] ?? "";
unset($_SESSION['success']);

/* FETCH DATA */
$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY phone");
$stock = mysqli_query($conn, "SELECT * FROM stock ORDER BY item_name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $order_status = $_POST['order_status'];
    $sales_date   = $_POST['sales_date'];

    $customer_phone = preg_replace('/[^0-9]/', '', $_POST['customer_phone']);
    $customer_name  = $_POST['customer_name'];
    $addr1 = $_POST['addr1'];
    $addr2 = $_POST['addr2'];
    $city  = $_POST['city'];

    $total_amount = max(0, $_POST['total_amount']);
    $paid_amount  = max(0, $_POST['paid_amount']);
    $balance      = max(0, $_POST['balance']);

    /* SAVE CUSTOMER IF NOT EXISTS */
    $chk = mysqli_query($conn, "SELECT id FROM customers WHERE phone='$customer_phone'");
    if (mysqli_num_rows($chk) == 0) {
        mysqli_query($conn, "
            INSERT INTO customers(phone,name,addr1,addr2,city,created_at)
            VALUES('$customer_phone','$customer_name','$addr1','$addr2','$city',NOW())
        ");
    }

    /* SAVE SALES */
    mysqli_query($conn, "
        INSERT INTO sales(order_status,sales_date,customer_phone,customer_name,addr1,addr2,city,
        total_amount,paid_amount,balance,created_at)
        VALUES('$order_status','$sales_date','$customer_phone','$customer_name','$addr1','$addr2','$city',
        '$total_amount','$paid_amount','$balance',NOW())
    ");

    $sale_id = mysqli_insert_id($conn);

    /* SAVE ITEMS + UPDATE STOCK */
    foreach ($_POST['stock_id'] as $i => $sid) {

        if (!$sid) continue;

        $qty   = max(1, $_POST['qty'][$i]);
        $price = $_POST['price'][$i];
        $tpr   = $_POST['tprice'][$i];

        mysqli_query($conn, "
            INSERT INTO sales_items(sale_id,item_name,qty,price_per_qty,total_price)
            SELECT $sale_id,item_name,'$qty','$price','$tpr'
            FROM stock WHERE id='$sid'
        ");

        mysqli_query($conn, "
            UPDATE stock SET total_stock = total_stock - $qty WHERE id='$sid'
        ");
    }

    $_SESSION['sales_success'] = "Sales created successfully!";
    header("Location:list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Sales</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f8
        }

        .container {
            /* max-width: 1100px; */
            margin: auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 6px
        }

        label {
            font-weight: bold;
            font-size: 13px
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px
        }

        input,
        select {
            padding: 8px;
            width: 100%
        }

        .btn {
            background: #2ecc71;
            color: #fff;
            border: none;
            padding: 8px 15px;
            cursor: pointer
        }

        .btn.gray {
            background: #7f8c8d
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 8px;
            margin-bottom: 8px
        }

        .remove {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px
        }

        .add-btn {
            cursor: pointer;
            color: #2980b9;
            font-weight: bold
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .6)
        }

        .modal-content {
            background: #fff;
            width: 400px;
            margin: 100px auto;
            padding: 20px
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">
            <h2>Create Sales</h2>
            <?= $success ? "<p style='color:green'>$success</p>" : "" ?>

            <form method="POST">

                <h3>Order</h3>
                <div class="grid-3">
                    <div>
                        <label>Order Status</label>
                        <select name="order_status" required>
                            <option>New</option>
                            <option>Invoiced</option>
                        </select>
                    </div>
                    <div>
                        <label>Sales Date</label>
                        <input type="date" name="sales_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <h3>Customer</h3>
                <div class="grid-3">
                    <div>
                        <label>Phone Number</label>
                        <select name="customer_phone" onchange="fillCustomer(this.value)" required>
                            <option value="">Select Phone</option>
                            <?php while ($c = mysqli_fetch_assoc($customers)): ?>
                                <option value="<?= $c['phone'] ?>"
                                    data-name="<?= $c['name'] ?>"
                                    data-addr1="<?= $c['addr1'] ?>"
                                    data-addr2="<?= $c['addr2'] ?>"
                                    data-city="<?= $c['city'] ?>">
                                    <?= $c['phone'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div><label>Name</label><input id="cname" name="customer_name" required></div>
                    <div><label>Address Line 1</label><input id="caddr1" name="addr1"></div>
                    <div><label>Address Line 2</label><input id="caddr2" name="addr2"></div>
                    <div><label>City</label><input id="ccity" name="city" required></div>
                </div>

                <button type="button" class="btn" onclick="openCustomer()">Add New Customer</button>
                <button type="button" class="btn gray" onclick="clearCustomer()">Clear</button>

                <hr>

                <h3>Items</h3>
                <div id="items"></div>
                <span class="add-btn" onclick="addItem()">+ Add Item</span>

                <hr>

                <h3>Payment</h3>
                <div class="grid-3">
                    <div><label>Total</label><input id="total_amount" name="total_amount" readonly></div>
                    <div><label>Paid</label><input id="paid_amount" name="paid_amount" onkeyup="calcBalance()" required></div>
                    <div><label>Balance</label><input id="balance" name="balance" readonly></div>
                </div>

                <br>
                <center><button class="btn">Save Sales</button>
                    <button type="reset" class="btn gray">Clear</button>
                </center>

            </form>
        </div>
    </div>

    <!-- CUSTOMER MODAL -->
    <div class="modal" id="custModal">
        <div class="modal-content">
            <h3>New Customer</h3>
            <label>Phone</label><input id="mphone" maxlength="10" oninput="onlyNumber(this)">
            <label>Name</label><input id="mname">
            <label>Address 1</label><input id="maddr1">
            <label>Address 2</label><input id="maddr2">
            <label>City</label><input id="mcity">
            <br><br>
            <button class="btn" onclick="saveCustomer()">Save</button>
        </div>
    </div>

    <script>
        function fillCustomer(phone) {
            let o = document.querySelector("option[value='" + phone + "']");
            if (!o) return;
            cname.value = o.dataset.name;
            caddr1.value = o.dataset.addr1;
            caddr2.value = o.dataset.addr2;
            ccity.value = o.dataset.city;
        }

        function onlyNumber(el) {
            el.value = el.value.replace(/[^0-9]/g, '')
        }

        function clearCustomer() {
            cname.value = caddr1.value = caddr2.value = ccity.value = ""
        }

        function openCustomer() {
            custModal.style.display = 'block'
        }

        function saveCustomer() {
            if (mphone.value.length != 10) {
                alert("Enter valid 10 digit phone");
                return;
            }
            document.querySelector('[name="customer_phone"]').innerHTML +=
                `<option selected value="${mphone.value}" data-name="${mname.value}"
data-addr1="${maddr1.value}" data-addr2="${maddr2.value}"
data-city="${mcity.value}">${mphone.value}</option>`;
            fillCustomer(mphone.value);
            custModal.style.display = 'none';
        }

        function addItem() {
            let d = document.createElement('div');
            d.className = 'item-row';
            d.innerHTML = `<select name="stock_id[]" onchange="this.nextElementSibling.nextElementSibling.value=this.selectedOptions[0].dataset.price||0">
<option value="">Item</option>
<?php mysqli_data_seek($stock, 0);
while ($s = mysqli_fetch_assoc($stock)): ?>
<option value="<?= $s['id'] ?>" data-price="<?= $s['selling_price'] ?>"><?= $s['item_name'] ?></option>
<?php endwhile; ?>
</select>
<input type="number" name="qty[]" value="1" min="1" onkeyup="calcRow(this)" required>
<input type="number" name="price[]" min="0" step="0.01" onkeyup="calcRow(this)" required>
<input type="number" name="tprice[]" readonly>
<button type="button" class="remove" onclick="this.parentNode.remove();calcTotal()">X</button>`;
            items.appendChild(d);
        }

        function calcRow(e) {
            let r = e.parentNode;
            r.children[3].value = (r.children[1].value * r.children[2].value).toFixed(2);
            calcTotal();
        }

        function calcTotal() {
            let t = 0;
            document.querySelectorAll('[name="tprice[]"]').forEach(i => t += +i.value || 0);
            total_amount.value = t.toFixed(2);
            calcBalance();
        }

        function calcBalance() {
            let t = +total_amount.value || 0;
            let p = +paid_amount.value || 0;
            if (p > t) {
                alert("Paid > Total");
                paid_amount.value = t;
                p = t;
            }
            balance.value = (t - p).toFixed(2);
        }

        addItem();
    </script>

    <?php include("../includes/footer.php"); ?>
</body>

</html>