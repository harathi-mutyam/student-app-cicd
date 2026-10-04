<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* SELECTED STUDENT FROM URL */
$selected_student = $_GET['student_id'] ?? '';

/* ADD MARKS */
if(isset($_POST['save'])){

    $student_id = $_POST['student_id'];
    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];

    mysqli_query($conn,
    "INSERT INTO marks(student_id,subject_name,marks)
    VALUES('$student_id','$subject_name','$marks')");

    echo "<script>
        alert('Marks Added Successfully');
        window.location='manage_marks.php';
    </script>";
}

/* DELETE MARKS */
if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    mysqli_query($conn,
    "DELETE FROM marks WHERE id='$id'");

    echo "<script>
        alert('Marks Deleted Successfully');
        window.location='manage_marks.php';
    </script>";
}

/* EDIT FETCH */
$editData = null;

if(isset($_GET['edit'])){

    $id = $_GET['edit'];

    $res = mysqli_query($conn,
    "SELECT * FROM marks WHERE id='$id'");

    $editData = mysqli_fetch_assoc($res);
}

/* UPDATE MARKS */
if(isset($_POST['update'])){

    $id = $_POST['id'];
    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];

    mysqli_query($conn,
    "UPDATE marks
     SET subject_name='$subject_name',
         marks='$marks'
     WHERE id='$id'");

    echo "<script>
        alert('Marks Updated Successfully');
        window.location='manage_marks.php';
    </script>";
}

/* STUDENTS */
$students = mysqli_query($conn,"SELECT * FROM students");

/* MARKS LIST */
$result = mysqli_query($conn,"SELECT * FROM marks");
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Marks</title>

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

input, select{
    width:100%;
    padding:10px;
    margin:10px 0;
}

button{
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    border:none;
    cursor:pointer;
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
    Manage Student Marks
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
<?php echo $editData ? "Edit Marks" : "Add Student Marks"; ?>
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

<input type="text"
name="subject_name"
placeholder="Enter Subject Name"
required
value="<?php echo $editData['subject_name'] ?? ''; ?>">

<input type="number"
name="marks"
placeholder="Enter Marks"
required
value="<?php echo $editData['marks'] ?? ''; ?>">

<button name="<?php echo $editData ? 'update' : 'save'; ?>">

<?php echo $editData ? "Update Marks" : "Save Marks"; ?>

</button>

</form>

</div>

<!-- TABLE -->
<div class="container">

<h2>All Student Marks</h2>

<table>

<tr>
    <th>ID</th>
    <th>Student ID</th>
    <th>Subject</th>
    <th>Marks</th>
    <th>Actions</th>
</tr>

<?php while($data=mysqli_fetch_assoc($result)){ ?>

<tr>
    <td><?php echo $data['id']; ?></td>
    <td><?php echo $data['student_id']; ?></td>
    <td><?php echo $data['subject_name']; ?></td>
    <td><?php echo $data['marks']; ?></td>
    <td>

        <a class="btn edit"
           href="manage_marks.php?edit=<?php echo $data['id']; ?>">
           Edit
        </a>

        <a class="btn delete"
           href="manage_marks.php?delete=<?php echo $data['id']; ?>"
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
