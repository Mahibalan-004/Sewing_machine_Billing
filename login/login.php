<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// If already logged in → redirect
if(isset($_SESSION['user_id'])){
    redirect("dashboard.php");
}

$error = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $username = clean($_POST['username']);
    $password = clean($_POST['password']);

    if($username == "" || $password == ""){
        $error = "Please fill all fields.";
    } else {

        // FIXED SQL — select password + role
        $sql = "SELECT id, username, password, role 
                FROM users 
                WHERE username='$username'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);

            // password check (plain for lab or md5)
            if($row['password'] == ($password) || $row['password'] == md5($password)){
                
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];

                redirect("dashboard.php");

            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Billing System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Login</h2>

        <?php if($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username">

            <label>Password</label>
            <input type="password" name="password">

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</div>

</body>
</html>
