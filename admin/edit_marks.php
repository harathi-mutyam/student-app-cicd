<?php
session_start();
include("../config/db.php");

/* Get mark id */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid Request");
}

/* Fetch existing mark */
$result = mysqli_query($conn,
"SELECT * FROM marks WHERE id=$id");

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Mark not found");
}

/* UPDATE logic */
if (isset($_POST['update'])) {

    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];

    /* Update marks */
    mysqli_query($conn, "
        UPDATE marks
        SET subject_name='$subject_name',
            marks='$marks'
        WHERE id=$id
    ");

    /* Get student_id from marks table */
    $student_id = $row['student_id'];

    /* Get actual students table ID */
    $student = mysqli_query($conn,
    "SELECT id FROM students
    WHERE student_id='$student_id'");

    $stu = mysqli_fetch_assoc($student);

    $student_table_id = $stu['id'];

    /* Redirect to student profile */
    header("Location: student_profile.php?id=".$student_table_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Marks</title>

    <link rel="stylesheet"
    href="../assets/css/style.css">

    <style>

        .container{
            width:400px;
            margin:80px auto;
            background:white;
            padding:25px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
        }

        input,
        button{
            width:100%;
            padding:10px;
            margin-top:10px;
        }

        button{
            background:#0d6efd;
            color:white;
            border:none;
            cursor:pointer;
        }

        button:hover{
            background:#084298;
        }

        .back-link{
            display:inline-block;
            margin-top:15px;
            text-decoration:none;
            color:#0d6efd;
        }

    </style>
</head>

<body>

<div class="container">

    <h2>Edit Marks</h2>

    <form method="POST">

        <label>Subject</label>

        <input type="text"
               name="subject_name"
               value="<?php echo $row['subject_name']; ?>"
               required>

        <label>Marks</label>

        <input type="number"
               name="marks"
               value="<?php echo $row['marks']; ?>"
               required>

        <button type="submit"
                name="update">
            Update
        </button>

    </form>

    <a class="back-link"
       href="javascript:history.back()">
       ⬅ Back
    </a>

</div>

</body>
</html>
