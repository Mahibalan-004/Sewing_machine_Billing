<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    redirect("../login/login.php");
}

/* ID CHECK */
if (!isset($_GET['id'])) {
    redirect("list.php");
}

$id = intval($_GET['id']);

/* FETCH DATA */
$sql = "SELECT * FROM stock WHERE id=$id LIMIT 1";
$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) == 0) {
    redirect("list.php");
}

$data = mysqli_fetch_assoc($res);

$success = "";
$error = "";

/* UPDATE SUBMIT */
if ($_SERVER['REQUEST_METHOD'] == "POST") {

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
            $imageName = time() . "_" . basename($_FILES['stock_image']['name']);
            $target = "../uploads/" . $imageName;
            if (move_uploaded_file($_FILES['stock_image']['tmp_name'], $target)) {
                $stock_image = $imageName;
            }
        }

        $update = "
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
            WHERE id=$id
        ";

        if (mysqli_query($conn, $update)) {
            header("Location: update.php?id=$id&success=1");
            exit();
        } else {
            $error = "Database Error!";
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Stock updated successfully!";
}
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
            font-weight: bold
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .6);
            justify-content: center;
            align-items: center
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 300px
        }

        .close {
            float: right;
            font-weight: bold;
            cursor: pointer
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2 align="center">Edit Stock Item</h2>

            <?php if ($success): ?>
                <div class="alert-success">✅ <?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <p style="color:red"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="section-title">Current Image</div>
                <?php if ($data['stock_image']): ?>
                    <img src="../uploads/<?= $data['stock_image'] ?>" width="100">
                    <?php else: ?>No Image<?php endif; ?>
                    <br><br>
                    <input type="file" name="stock_image">

                    <div class="section-title">Item Details</div>
                    <div class="form-grid grid-3">

                        <div>
                            <label>Item Name *</label>
                            <input type="text" name="item_name" value="<?= $data['item_name'] ?>" required>
                        </div>

                        <div>
                            <label>Brand</label>
                            <select name="brand" id="brand">
                                <option value="<?= $data['brand'] ?>"><?= $data['brand'] ?></option>
                            </select>
                            <button type="button" class="btn" onclick="openBrand()">+ Add Brand</button>
                        </div>

                        <div>
                            <label>Model</label>
                            <select name="model" id="model">
                                <option value="<?= $data['model'] ?>"><?= $data['model'] ?></option>
                            </select>
                            <button type="button" class="btn" onclick="openModel()">+ Add Model</button>
                        </div>

                    </div>

                    <div class="section-title">Stock Quantities</div>
                    <div class="form-grid grid-3">
                        <div><label>Old Stock</label><input value="<?= $data['total_stock'] ?>" disabled></div>
                        <div><label>Add New Stock</label><input type="number" name="new_stock" id="new_stock" value="0" onkeyup="calcStock()"></div>
                        <div><label>Total Stock</label><input id="total_stock" value="<?= $data['total_stock'] ?>" disabled></div>
                    </div>

                    <label>Reorder Level</label>
                    <input type="number" name="min_quantity" value="<?= $data['min_quantity'] ?>">

                    <div class="section-title">Price Details</div>
                    <div class="form-grid grid-3">
                        <div><label>Purchase Price</label><input type="number" step="0.01" name="purchase_price" id="purchase_price" value="<?= $data['purchase_price'] ?>" onkeyup="calcTotal()"></div>
                        <div><label>Actual Price</label><input type="number" step="0.01" name="actual_price" value="<?= $data['actual_price'] ?>"></div>
                        <div><label>Selling Price</label><input type="number" step="0.01" name="selling_price" value="<?= $data['selling_price'] ?>"></div>
                    </div>

                    <div class="form-grid grid-2">
                        <div><label>GST %</label><input type="number" step="0.01" name="gst_percent" id="gst_percent" value="<?= $data['gst_percent'] ?>" onkeyup="calcTotal()"></div>
                        <div><label>Total Price</label><input name="total_price" id="total_price" value="<?= $data['total_price'] ?>" readonly></div>
                    </div>

                    <label>Warranty (Months)</label>
                    <input type="number" name="warranty_months" value="<?= $data['warranty_months'] ?>">

                    <br><br>
                    <button class="btn">Update Stock</button>
                    <a href="list.php" class="btn" style="background:#7f8c8d">Back</a>

            </form>
        </div>
    </div>

    <!-- BRAND MODAL -->
    <div class="modal" id="brandModal">
        <div class="modal-content">
            <span class="close" onclick="closeBrand()">X</span>
            <h3>Add Brand</h3>
            <input type="text" id="newBrand">
            <button class="btn" onclick="saveBrand()">Save</button>
        </div>
    </div>

    <!-- MODEL MODAL -->
    <div class="modal" id="modelModal">
        <div class="modal-content">
            <span class="close" onclick="closeModel()">X</span>
            <h3>Add Model</h3>
            <input type="text" id="newModel">
            <button class="btn" onclick="saveModel()">Save</button>
        </div>
    </div>

    <script>
        function calcStock() {
            var oldStock = <?= $data['total_stock'] ?>;
            var n = parseInt(new_stock.value) || 0;
            total_stock.value = oldStock + n;
        }

        function calcTotal() {
            var p = parseFloat(purchase_price.value) || 0;
            var g = parseFloat(gst_percent.value) || 0;
            var q = parseInt(new_stock.value) || 0;
            total_price.value = ((p + (p * g / 100)) * q).toFixed(2);
        }

        function openBrand() {
            brandModal.style.display = "flex";
        }

        function closeBrand() {
            brandModal.style.display = "none";
        }

        function saveBrand() {
            var b = newBrand.value;
            if (!b) return;
            brand.add(new Option(b, b));
            brand.value = b;
            closeBrand();
        }

        function openModel() {
            modelModal.style.display = "flex";
        }

        function closeModel() {
            modelModal.style.display = "none";
        }

        function saveModel() {
            var m = newModel.value;
            if (!m) return;
            model.add(new Option(m, m));
            model.value = m;
            closeModel();
        }
    </script>

    <?php include("../includes/footer.php"); ?>
</body>

</html>