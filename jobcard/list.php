<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    redirect("../login/login.php");
}

/* ================= SUCCESS MESSAGE ================= */
$success = "";
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

/* ================= SEARCH ================= */
$search = "";
$where  = "";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = clean($_GET['search']);
    $where = "WHERE customer_phone LIKE '%$search%' 
              OR jobcard_no LIKE '%$search%'";
}

/* ================= FETCH JOBCARDS ================= */
$query  = "SELECT * FROM jobcards $where ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Jobcard List</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-box input {
            flex: 1;
            padding: 8px;
        }
    </style>
</head>

<body>

    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2 align="center">Jobcard List</h2>

            <?php if ($success != ""): ?>
                <p style="color:green"><?= $success ?></p>
            <?php endif; ?>

            <!-- ================= SEARCH FORM ================= -->
            <form method="GET" class="search-box">
                <input type="text" name="search"
                    placeholder="Search by Phone or Jobcard No"
                    value="<?= htmlspecialchars($search) ?>">
                <button class="btn">Search</button>
                <a href="list.php" class="btn" style="background:#7f8c8d">Reset</a>
            </form>

            <center><a href="create.php" class="btn" style="margin-bottom:15px;">+ Create New Jobcard</a></center>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Jobcard No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Machine</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['jobcard_no']) ?></td>
                            <td><?= date("d-m-Y", strtotime($row['jobcard_date'])) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($row['machine_name']) ?></td>
                            <td><?= htmlspecialchars($row['job_status']) ?></td>
                            <td>
                                <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:#999;">
                            No Jobcards Found
                        </td>
                    </tr>
                <?php endif; ?>

            </table>

        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>