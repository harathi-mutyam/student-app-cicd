<?php
session_start();
include("../config/db.php");

/* Get attendance id */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid Request");
}

/* Fetch attendance record */
$result = mysqli_query($conn,
"SELECT * FROM attendance WHERE id=$id");

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Attendance not found");
}

/* Update Logic */
if(isset($_POST['update'])){

    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];

    mysqli_query($conn,"
    UPDATE attendance
    SET attendance_date='$attendance_date',
        status='$status'
    WHERE id=$id
    ");

    /* Get student_id */
    $student_id = $row['student_id'];

    /* Get students table ID */
    $student = mysqli_query($conn,
    "SELECT id FROM students
    WHERE student_id='$student_id'");

    $stu = mysqli_fetch_assoc($student);

    $student_table_id = $stu['id'];

    /* Redirect */
    header("Location: student_profile.php?id=".$student_table_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Attendance</title>

<link rel="stylesheet"
href="../assets/css/style.css">

<style>

.container{
    width:400px;
    margin:80px auto;
    background:white;
    padding:25px;
    border-radius:10px;
}

input,
select,
button{
    width:100%;
    padding:10px;
    margin-top:10px;
}

button{
    background:#0d6efd;
    color:white;
    border:none;
}

</style>

</head>

<body>

<div class="container">

<h2>Edit Attendance</h2>

<form method="POST">

<label>Date</label>

<input type="date"
name="attendance_date"
value="<?php echo $row['attendance_date']; ?>"
required>

<label>Status</label>

<select name="status" required>

<option value="Present"
<?php if($row['status']=="Present") echo "selected"; ?>>
Present
</option>

<option value="Absent"
<?php if($row['status']=="Absent") echo "selected"; ?>>
Absent
</option>

</select>

<button type="submit" name="update">
Update Attendance
</button>

</form>

</div>

</body>
</html>
