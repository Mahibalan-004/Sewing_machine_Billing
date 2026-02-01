<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// If already logged in
if(isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $username = clean($_POST['username']);
    $password = clean($_POST['password']);

    if($username == "" || $password == ""){
        $error = "Please fill all fields.";
    } 
    else {

        $sql = "SELECT id, username, password FROM users WHERE username='$username'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);

            // password check
            if($row['password'] == $password || $row['password'] == md5($password)){

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                // SUCCESS MESSAGE
                $success = "Login successful! Redirecting to Dashboard...";
                echo "<script>
                        alert('Login Successful!');
                        window.location.href = 'dashboard.php';
                      </script>";
                exit;

            } else {
                $error = "❌ Wrong Password!";
            }
        } 

    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Billing System</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-container">
    <h2>Billing System Login</h2>

    <?php if($error != "") { ?>
        <script>alert("<?php echo $error; ?>");</script>
    <?php } ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="login-btn">Login</button>
    </form>
</div>

</body>
</html>
