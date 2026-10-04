<?php
session_start();
include("../config/db.php");

if(isset($_POST['login'])){

        #$email=$_POST['email'];
$student_id = $_POST['student_id'];
$password=$_POST['password'];

$query=mysqli_query($conn,
"SELECT * FROM students
WHERE student_id='$student_id'
AND password='$password'");

if(mysqli_num_rows($query)>0){

$row=mysqli_fetch_assoc($query);

$_SESSION['student_id']=$row['student_id'];

header("Location: student_dashboard.php");

}else{
echo "Invalid Login";
}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container">

<h2>Student Login</h2>

<form method="POST">

<input type="student_id"
name="student_id"
placeholder="Enter your Student ID"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button name="login">Login</button>

</form>
   <!-- Forgot Password -->
    <p style="margin-top:10px;">

        <a href="#"
           onclick="showForgotPasswordAlert()">

            Forgot Password?

        </a>

    </p>
</div>
<script>

function showForgotPasswordAlert() {

    alert("If you forgot your password, please contact the admin.");

}

</script>

</body>
</html>
