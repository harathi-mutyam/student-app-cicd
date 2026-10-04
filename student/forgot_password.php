<?php
session_start();
include("../config/db.php");

if(isset($_POST['check'])){

    $student_id = $_POST['student_id'];
    $email = $_POST['email'];

    $query = mysqli_query($conn,
    "SELECT * FROM students
     WHERE student_id='$student_id'
     AND email='$email'");

    if(mysqli_num_rows($query) > 0){

        $_SESSION['reset_student_id'] = $student_id;

        header("Location: reset_password.php");
        exit();

    } else {
        echo "<script>alert('Invalid Student ID or Email');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container">

<h2>Forgot Password</h2>

<form method="POST">

<input type="text" name="student_id" placeholder="Student ID" required>

<input type="email" name="email" placeholder="Registered Email" required>

<button name="check">Verify</button>

</form>

</div>

</body>
</html>
