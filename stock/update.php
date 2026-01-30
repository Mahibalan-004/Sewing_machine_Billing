<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    redirect("../login/login.php");
}

/* ================= ID CHECK ================= */
if (!isset($_GET['id'])) {
    redirect("list.php");
}

$id = intval($_GET['id']);

/* ================= FETCH DATA ================= */
$q = "SELECT * FROM stock WHERE id=$id LIMIT 1";
$r = mysqli_query($conn, $q);
if (mysqli_num_rows($r) == 0) redirect("list.php");

$data = mysqli_fetch_assoc($r);

$success = "";
$error   = "";

/* ================= SUCCESS ALERT ================= */
if (isset($_GET['updated'])) {
    $success = "✅ Stock updated successfully!";
}

/* ================= UPDATE ================= */
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $item_name     = clean($_POST['item_name']);
    $brand         = clean($_POST['brand']);
    $model         = clean($_POST['model']);
    $new_stock     = intval($_POST['new_stock']);
    $min_quantity  = intval($_POST['min_quantity']);

    $old_stock   = intval($data['total_stock']);
    $total_stock = $old_stock + $new_stock;

    $purchase      = floatval($_POST['purchase_price']);
    $actual_price  = floatval($_POST['actual_price']);
    $selling_price = floatval($_POST['selling_price']);
    $gst           = floatval($_POST['gst_percent']);
    $total_price   = floatval($_POST['total_price']);
    $warranty      = intval($_POST['warranty_months']);

    if ($item_name == "") {
        $error = "Item name is required!";
    } else {

        /* IMAGE */
        $stock_image = $data['stock_image'];
        if (!empty($_FILES['stock_image']['name'])) {
            $img = time() . "_" . $_FILES['stock_image']['name'];
            if (move_uploaded_file($_FILES['stock_image']['tmp_name'], "../uploads/" . $img)) {
                $stock_image = $img;
            }
        }

        $sql = "
        UPDATE stock SET
            item_name='$item_name',
            brand='$brand',
            model='$model',
            stock_image='$stock_image',
            old_stock='$old_stock',
            new_stock='$new_stock',
            total_stock='$total_stock',
            min_quantity='$min_quantity',
            purchase_price='$purchase',
            actual_price='$actual_price',
            selling_price='$selling_price',
            gst_percent='$gst',
            total_price='$total_price',
            warranty_months='$warranty'
        WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            header("Location: update.php?id=$id&updated=1");
            exit();
        } else {
            $error = "Database Error!";
        }
    }
}

/* REFRESH DATA */
$r = mysqli_query($conn, $q);
$data = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Stock</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .form-grid {
            display: grid;
            gap: 15px
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr)
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr)
        }

        .section-title {
            margin: 25px 0 10px;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <center>
                <h2>Edit Stock Item</h2>
            </center>

            <?php if ($success): ?><div class="alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- IMAGE -->
                <div class="section-title">Stock Image</div>
                <?php if ($data['stock_image']): ?>
                    <img src="../uploads/<?= $data['stock_image'] ?>" width="100"><br>
                <?php endif; ?>
                <input type="file" name="stock_image">

                <!-- ITEM -->
                <div class="section-title">Item De  tails</div>
                <div class="form-grid grid-3">
                    <input type="text" name="item_name" value="<?= $data['item_name'] ?>" placeholder="Item Name" required>

                    <select name="brand">
                        <option value="">Select Brand</option>
                        <option <?= ($data['brand'] == "Usha") ? "selected" : "" ?>>Usha</option>
                        <option <?= ($data['brand'] == "Jack") ? "selected" : "" ?>>Jack</option>
                        <option <?= ($data['brand'] == "Singer") ? "selected" : "" ?>>Singer</option>
                        <option <?= ($data['brand'] == "Brother") ? "selected" : "" ?>>Brother</option>
                    </select>

                    <select name="model">
                        <option value="">Select Model</option>
                        <option <?= ($data['model'] == "Zigzag") ? "selected" : "" ?>>Zigzag</option>
                        <option <?= ($data['model'] == "Domestic") ? "selected" : "" ?>>Domestic</option>
                        <option <?= ($data['model'] == "Industrial") ? "selected" : "" ?>>Industrial</option>
                    </select>
                </div>

                <!-- STOCK -->
                <div class="section-title">Stock Quantity</div>
                <div class="form-grid grid-3">
                    <input value="<?= $data['total_stock'] ?>" disabled>
                    <input type="number" name="new_stock" id="new_stock" value="0" onkeyup="calcStock()">
                    <input id="total_stock" value="<?= $data['total_stock'] ?>" disabled>
                </div>

                <input type="number" name="min_quantity" value="<?= $data['min_quantity'] ?>" placeholder="Reorder Level">

                <!-- PRICE -->
                <div class="section-title">Price Details</div>
                <div class="form-grid grid-3">
                    <input type="number" step="0.01" name="purchase_price" id="purchase_price"
                        value="<?= $data['purchase_price'] ?>" onkeyup="calcTotal()" placeholder="Purchase Price">

                    <input type="number" step="0.01" name="actual_price"
                        value="<?= $data['actual_price'] ?>" placeholder="Actual Price">

                    <input type="number" step="0.01" name="selling_price"
                        value="<?= $data['selling_price'] ?>" placeholder="Selling Price">
                </div>

                <div class="form-grid grid-2">
                    <input type="number" step="0.01" name="gst_percent" id="gst_percent"
                        value="<?= $data['gst_percent'] ?>" onkeyup="calcTotal()" placeholder="GST %">

                    <input type="number" id="total_price" name="total_price"
                        value="<?= $data['total_price'] ?>" readonly placeholder="Total Price">
                </div>

                <!-- WARRANTY -->
                <div class="section-title">Warranty</div>
                <input type="number" name="warranty_months" value="<?= $data['warranty_months'] ?>">

                <br>
                <button class="btn">Update Stock</button>
                <a href="list.php" class="btn" style="background:#7f8c8d">Back</a>

            </form>
        </div>
    </div>

    <script>
        function calcStock() {
            var old = <?= $data['total_stock'] ?>;
            var n = parseInt(new_stock.value) || 0;
            total_stock.value = old + n;
        }

        function calcTotal() {
            var p = parseFloat(purchase_price.value) || 0;
            var g = parseFloat(gst_percent.value) || 0;
            var q = parseInt(new_stock.value) || 0;
            total_price.value = ((p + (p * g / 100)) * q).toFixed(2);
        }
    </script>

    <?php include("../includes/footer.php"); ?>
</body>

</html>