<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

/* Student info */
$student = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT * FROM students WHERE student_id='$student_id'")
);

/* Marks */
$marks = mysqli_query($conn,
"SELECT * FROM marks WHERE student_id='$student_id'");

/* Attendance */
$attendance = mysqli_query($conn,
"SELECT * FROM attendance WHERE student_id='$student_id'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>

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

.navbar a{
    float:right;
    margin-right:15px;
    color:white;
    text-decoration:none;
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

<div class="navbar">
    Welcome <?= $student['full_name'] ?>

    <a href="logout.php">Logout</a>
</div>

<div class="dashboard-box">

<h3>Quick Menu</h3>

<div class="menu">
    <a href="view_marks.php">Marks</a>
    <a href="view_attendance.php">Attendance</a>
    <a href="change_password.php">Change Password</a>
</div>

</div>

<div class="dashboard-box">

<h3>My Marks</h3>

<table>
<tr><th>Subject</th><th>Marks</th></tr>

<?php if(mysqli_num_rows($marks) > 0){ ?>
    <?php while($m = mysqli_fetch_assoc($marks)){ ?>
    <tr>
        <td><?= $m['subject_name'] ?></td>
        <td><?= $m['marks'] ?></td>
    </tr>
    <?php } ?>
<?php } else { ?>
<tr><td colspan="2">No marks available</td></tr>
<?php } ?>

</table>

</div>

<div class="dashboard-box">

<h3>My Attendance</h3>

<table>
<tr><th>Date</th><th>Status</th></tr>

<?php if(mysqli_num_rows($attendance) > 0){ ?>
    <?php while($a = mysqli_fetch_assoc($attendance)){ ?>
    <tr>
        <td><?= $a['attendance_date'] ?></td>
        <td><?= $a['status'] ?></td>
    </tr>
    <?php } ?>
<?php } else { ?>
<tr><td colspan="2">No attendance available</td></tr>
<?php } ?>

</table>

</div>

</body>
</html>
