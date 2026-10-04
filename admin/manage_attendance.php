<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* SELECTED STUDENT FROM URL */
$selected_student = $_GET['student_id'] ?? '';

/* ADD ATTENDANCE */
if(isset($_POST['save'])){

    $student_id = $_POST['student_id'];
    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];

    mysqli_query($conn,
    "INSERT INTO attendance(student_id,attendance_date,status)
    VALUES('$student_id','$attendance_date','$status')");

    echo "<script>
        alert('Attendance Saved Successfully');
        window.location='manage_attendance.php';
    </script>";
}

/* DELETE ATTENDANCE */
if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    mysqli_query($conn,
    "DELETE FROM attendance WHERE id='$id'");

    echo "<script>
        alert('Attendance Deleted');
        window.location='manage_attendance.php';
    </script>";
}

/* EDIT FETCH */
$editData = null;

if(isset($_GET['edit'])){

    $id = $_GET['edit'];

    $res = mysqli_query($conn,
    "SELECT * FROM attendance WHERE id='$id'");

    $editData = mysqli_fetch_assoc($res);
}

/* UPDATE ATTENDANCE */
if(isset($_POST['update'])){

    $id = $_POST['id'];
    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];

    mysqli_query($conn,
    "UPDATE attendance
     SET attendance_date='$attendance_date',
         status='$status'
     WHERE id='$id'");

    echo "<script>
        alert('Attendance Updated');
        window.location='manage_attendance.php';
    </script>";
}

/* STUDENTS */
$students = mysqli_query($conn,"SELECT * FROM students");

/* ATTENDANCE LIST */
$result = mysqli_query($conn,"SELECT * FROM attendance");
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Attendance</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

.navbar{
    background:#0d6efd;
    color:white;
    padding:15px;
}

.menu{
    text-align:center;
    margin-top:20px;
}

.menu a{
    margin:10px;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

.container{
    width:40%;
    margin:20px auto;
    background:white;
    padding:20px;
    border-radius:10px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
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

.btn{
    padding:5px 8px;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

.edit{ background:green; }
.delete{ background:red; }

</style>

</head>

<body>

<div class="navbar">
    Manage Attendance
</div>

<!-- MENU -->
<div class="menu">

    <a href="admin_dashboard.php">Dashboard</a>
    <a href="add_student.php">Add Student</a>
    <a href="manage_marks.php">Manage Marks</a>
    <a href="manage_attendance.php">Manage Attendance</a>
    <a href="logout.php">Logout</a>

</div>

<!-- FORM -->
<div class="container">

<h2>
<?php echo $editData ? "Edit Attendance" : "Add Attendance"; ?>
</h2>

<form method="POST">

<?php if($editData){ ?>
<input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
<?php } ?>

<select name="student_id" required <?php echo $editData ? "disabled" : ""; ?>>

<option value="">Select Student</option>

<?php while($row=mysqli_fetch_assoc($students)){ ?>

<option value="<?php echo $row['student_id']; ?>"
<?php
if($row['student_id'] == $selected_student) echo "selected";
if($editData && $editData['student_id']==$row['student_id']) echo "selected";
?>>

<?php echo $row['student_id']; ?> - <?php echo $row['full_name']; ?>

</option>

<?php } ?>

</select>

<input type="date"
name="attendance_date"
required
value="<?php echo $editData['attendance_date'] ?? ''; ?>">

<select name="status" required>

<option value="">Select Status</option>

<option value="Present"
<?php if($editData && $editData['status']=="Present") echo "selected"; ?>>
Present
</option>

<option value="Absent"
<?php if($editData && $editData['status']=="Absent") echo "selected"; ?>>
Absent
</option>

</select>

<button name="<?php echo $editData ? 'update' : 'save'; ?>">

<?php echo $editData ? "Update Attendance" : "Save Attendance"; ?>

</button>

</form>

</div>

<!-- TABLE -->
<div class="container">

<h2>Attendance Records</h2>

<table>

<tr>
    <th>ID</th>
    <th>Student ID</th>
    <th>Date</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while($data=mysqli_fetch_assoc($result)){ ?>

<tr>
    <td><?php echo $data['id']; ?></td>
    <td><?php echo $data['student_id']; ?></td>
    <td><?php echo $data['attendance_date']; ?></td>
    <td><?php echo $data['status']; ?></td>
    <td>

        <a class="btn edit"
           href="manage_attendance.php?edit=<?php echo $data['id']; ?>">
           Edit
        </a>

        <a class="btn delete"
           href="manage_attendance.php?delete=<?php echo $data['id']; ?>"
           onclick="return confirm('Delete this record?');">
           Delete
        </a>

    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>
