<?php
session_start();
include_once 'database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Teacher') {
    header('Location: ./logout.php');
    exit();
}

/* =========================
   AUTO GENERATE STUDENT ID
========================= */
function generateStudentID($conn) {
    $sql = "SELECT sid FROM student ORDER BY sid DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $lastID = $row['sid'];
        $num = (int)substr($lastID, 3);
        return "STU" . ($num + 1);
    }

    return "STU1001";
}

/* =========================
   VARIABLES
========================= */
$isUpdate = isset($_GET['update']);
$old_sid = "";

$sid = $fname = $lname = $classroom = $dob = $gender = $address = $parent = $email = "";

/* =========================
   FETCH DATA (EDIT MODE)
========================= */
if ($isUpdate) {
    $old_sid = $_GET['update'];

    $sql = "SELECT * FROM student WHERE sid='$old_sid'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $sid = $row['sid'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $classroom = $row['classroom'];
        $email = $row['email'];
        $dob = date_format(new DateTime($row['bday']), 'Y-m-d');
        $gender = $row['gender'];
        $address = $row['address'];
        $parent = $row['parent'];
    }
}

/* =========================
   SUBMIT (ADD / UPDATE)
========================= */
if (isset($_POST['submit'])) {

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $classroom = $_POST['classroom'];
    $dob = date_format(new DateTime($_POST['dob']), 'Y-m-d');
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $parent = $_POST['parent'];

    if (!$isUpdate) {

        // ADD
        $sid = generateStudentID($conn);

        $sql = "INSERT INTO student 
        (sid, fname, lname, bday, address, gender, parent, classroom, email)
        VALUES 
        ('$sid','$fname','$lname','$dob','$address','$gender','$parent','$classroom','$email')";

        if ($conn->query($sql)) {
            echo "<script>alert('Student Added Successfully'); window.location='student.php';</script>";
        }

    } else {

        // UPDATE
        $sql = "UPDATE student SET 
        fname='$fname',
        lname='$lname',
        bday='$dob',
        address='$address',
        gender='$gender',
        parent='$parent',
        classroom='$classroom',
        email='$email'
        WHERE sid='$old_sid'";

        if ($conn->query($sql)) {
            echo "<script>alert('Student Updated Successfully'); window.location='student.php';</script>";
        }
    }
}

/* =========================
   PAGINATION
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) as total FROM student");
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT * FROM student LIMIT $start, $limit";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <?php include_once 'header.php'; ?>
</head>

<body class="nav-md">
<div class="container body">
<div class="main_container">

    <div class="col-md-3 left_col">
        <?php include_once 'sidebar.php'; ?>
    </div>

    <?php include_once 'nav-menu.php'; ?>

    <div class="right_col" role="main">

        <div class="row">

            <!-- FORM -->
            <div class="col-md-3">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= $isUpdate ? "Update Student" : "Add Student" ?></h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                        <form method="POST">

                            <!-- SID DISPLAY -->
                            <?php if ($isUpdate): ?>
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <p><strong><?= $sid ?></strong></p>
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <p><em>Auto-generated (STU1001...)</em></p>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>First Name</label>
                                <input name="fname" class="form-control" required value="<?= $fname ?>">
                            </div>

                            <div class="form-group">
                                <label>Last Name</label>
                                <input name="lname" class="form-control" required value="<?= $lname ?>">
                            </div>

                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= $dob ?>">
                            </div>

                            <div class="form-group">
                                <label>Gender</label><br>
                                <label>
                                    <input type="radio" name="gender" value="Male" <?= ($gender=='Male')?'checked':'' ?>> Male
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="Female" <?= ($gender=='Female')?'checked':'' ?>> Female
                                </label>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input name="email" class="form-control" value="<?= $email ?>">
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control"><?= $address ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Classroom</label>
                                <select name="classroom" class="form-control">
                                    <option value="">Select Class</option>
                                    <?php
                                    $cls = $conn->query("SELECT * FROM classroom");
                                    while ($rowc = $cls->fetch_assoc()) {
                                        $selected = ($classroom == $rowc['hno']) ? "selected" : "";
                                        echo "<option value='{$rowc['hno']}' $selected>{$rowc['title']} - {$rowc['hno']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Parent</label>
                                <select name="parent" class="form-control">
                                    <option value="0">Select Parent</option>
                                    <?php
                                    $par = $conn->query("SELECT * FROM parent");
                                    while ($rowp = $par->fetch_assoc()) {
                                        $selected = ($parent == $rowp['pid']) ? "selected" : "";
                                        echo "<option value='{$rowp['pid']}' $selected>{$rowp['fname']} {$rowp['lname']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary">
                                <?= $isUpdate ? "Update Student" : "Add Student" ?>
                            </button>

                        </form>

                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="col-md-9">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>All Students</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <th>Address</th>
                                    <th>Classroom</th>
                                    <th>Parent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['sid'] ?></td>
                                        <td><?= $row['fname'] ?> <?= $row['lname'] ?></td>
                                        <td><?= $row['bday'] ?></td>
                                        <td><?= $row['gender'] ?></td>
                                        <td><?= $row['address'] ?></td>
                                        <td><?= $row['classroom'] ?></td>
                                        <td><?= $row['parent'] ?></td>
                                        <td>
                                            <a href="student.php?update=<?= $row['sid'] ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>

                        </table>

                        <!-- PAGINATION -->
                        <nav>
                            <ul class="pagination">
                                <li class="<?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a href="?page=<?= $page-1 ?>">Previous</a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="<?= ($page == $i) ? 'active' : '' ?>">
                                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a href="?page=<?= $page+1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php include_once 'footer.php'; ?>

</div>
</div>
</body>
</html>