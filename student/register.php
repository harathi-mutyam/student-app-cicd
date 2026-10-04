<?php
session_start();
include("../config/db.php");

if(isset($_POST['register'])){

    $student_id = $_POST['student_id'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // CHECK IF STUDENT EXISTS
    $check = mysqli_query($conn,
    "SELECT * FROM students WHERE student_id='$student_id'");

    if(mysqli_num_rows($check) > 0){

        echo "<script>
            alert('Already registered user. Please login or reset password.');
            window.location.href='student_login.php';
        </script>";
        exit();

    } else {

        echo "<script>
            alert('Invalid Student ID. Contact admin.');
            window.location.href='student_login.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Registration</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container">

<h2>Student Registration</h2>

<form method="POST">

<input type="text" name="student_id" placeholder="Student ID" required>

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button name="register">Register</button>

</form>

</div>

</body>
</html>
