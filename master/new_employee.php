<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Only ADMIN can add employees
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    redirect("../login/login.php");
}

// Auto-generate employee code
$q = mysqli_query($conn, "SELECT id FROM employees ORDER BY id DESC LIMIT 1");
$nextID = (mysqli_num_rows($q) > 0) ? mysqli_fetch_assoc($q)['id'] + 1 : 1;
$emp_code = "EMP-" . date("Y") . "-" . str_pad($nextID, 3, "0", STR_PAD_LEFT);

$success = "";
$error = "";

// On Submit
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name  = clean($_POST['name']);
    $mobile = clean($_POST['mobile']);
    $email  = clean($_POST['email']);
    $address = clean($_POST['address']);
    $role = clean($_POST['role']);
    $username = clean($_POST['username']);
    $password = clean($_POST['password']);
    $status = clean($_POST['status']);

    if($name == "" || $username == "" || $password == ""){
        $error = "Name, Username and Password are required.";
    } else {

        $ins = "
            INSERT INTO employees (
                emp_code, name, mobile, email, address,
                role, username, password, status, created_at
            ) VALUES (
                '$emp_code', '$name', '$mobile', '$email', '$address',
                '$role', '$username', '$password', '$status', NOW()
            )
        ";

        if(mysqli_query($conn, $ins)){
            $success = "Employee added successfully!";
        } else {
            $error = "Database error!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Employee</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">

        <h2>Add New Employee</h2>

        <?php if($success!=""){ echo "<p style='color:green;'>$success</p>"; } ?>
        <?php if($error!=""){ echo "<p style='color:red;'>$error</p>"; } ?>

        <form method="POST">

            <label>Employee Code</label>
            <input type="text" value="<?php echo $emp_code; ?>" disabled>

            <label>Employee Name *</label>
            <input type="text" name="name" required>

            <label>Mobile Number</label>
            <input type="text" name="mobile">

            <label>Email</label>
            <input type="text" name="email">

            <label>Address</label>
            <textarea name="address"></textarea>

            <label>Role</label>
            <select name="role">
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>

            <label>Username *</label>
            <input type="text" name="username" required>

            <label>Password *</label>
            <input type="password" name="password" required>

            <label>Status</label>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <br><br>
            <button type="submit" class="btn">Add Employee</button>
            <button type="reset" class="btn" style="background:#e74c3c;">Reset</button>

        </form>

    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
