<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

/* Student Info */
$student = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT * FROM students WHERE student_id='$student_id'")
);

/* Marks */
$query = mysqli_query($conn,
"SELECT * FROM marks
WHERE student_id='$student_id'");
?>

<!DOCTYPE html>
<html>
<head>

<title>Marks</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

.dashboard-box{
    width:90%;
    margin:20px auto;
    background:white;
    padding:20px;
    border-radius:10px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#0d6efd;
    color:white;
    padding:10px;
}

td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

.navbar{
    background:#0d6efd;
    color:white;
    padding:15px;
}

.navbar a{
    float:right;
    margin-right:15px;
    color:white;
    text-decoration:none;
}

.menu{
    margin-top:10px;
}

.menu a{
    margin-right:10px;
    padding:8px 12px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

</style>

</head>

<body>

<!-- Top Navbar -->
<div class="navbar">

    Welcome <?= $student['full_name'] ?>

    <a href="logout.php">Logout</a>

</div>

<!-- Main Menu -->
<div class="dashboard-box">

    <h3>Quick Menu</h3>

    <div class="menu">

        <a href="student_dashboard.php">Dashboard</a>

        <a href="view_marks.php">Marks</a>

        <a href="view_attendance.php">Attendance</a>

        <a href="change_password.php">Change Password</a>

    </div>

</div>

<!-- Marks Table -->
<div class="dashboard-box">

<h2>My Marks</h2>

<table>

<tr>
    <th>Subject</th>
    <th>Marks</th>
</tr>

<?php
if(mysqli_num_rows($query) > 0){

    while($row = mysqli_fetch_assoc($query)){
?>

<tr>

    <td><?php echo $row['subject_name']; ?></td>

    <td><?php echo $row['marks']; ?></td>

</tr>

<?php
    }

}else{
?>

<tr>
    <td colspan="2">No marks available</td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>
