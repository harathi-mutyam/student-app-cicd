<?php
session_start();
include("../config/db.php");

if(isset($_POST['login'])){

    $username=$_POST['username'];
    $password=$_POST['password'];

    $query=mysqli_query($conn,
    "SELECT * FROM admins
    WHERE username='$username'
    AND password='$password'");

    if(mysqli_num_rows($query)>0){

        $_SESSION['admin']=$username;

        header("Location: admin_dashboard.php");
    }else{
        echo "Invalid Login";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

<h2>Admin Login</h2>

<form method="POST">

<input type="text"
name="username"
placeholder="Enter Username"
required>

<input type="password"
name="password"
placeholder="Enter Password"
required>

<button name="login">Login</button>

</form>

</div>

</body>
</html>
