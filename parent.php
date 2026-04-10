<?php
session_start();
include_once 'database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Teacher') {
    header('Location: ./logout.php');
    exit();
}

/* =========================
   INITIALIZE VARIABLES
========================= */
$pid = "";
$fname = "";
$lname = "";
$nic = "";
$gender = "";
$email = "";
$address = "";
$contact = "";
$occupation = "";

/* =========================
   FETCH DATA FOR UPDATE
========================= */
if (isset($_GET['update'])) {

    $pid = $_GET['update'];

    $sql = "SELECT * FROM parent WHERE pid='$pid'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $pid = $row['pid'];
        $nic = $row['nic'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $contact = $row['contact'];
        $occupation = $row['job'];
        $gender = $row['gender'];
        $address = $row['address'];
        $email = $row['email'];
    }
}

/* =========================
   ADD / UPDATE
========================= */
if (isset($_POST['submit'])) {

    $nic = $_POST['nic'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $occupation = $_POST['job'];

    if (!isset($_GET['update'])) {

        /* ADD PARENT */
        $sql = "INSERT INTO parent 
        (fname, lname, address, gender, job, contact, nic, email)
        VALUES 
        ('$fname','$lname','$address','$gender','$occupation','$contact','$nic','$email')";

        if ($conn->query($sql)) {
            echo "<script>alert('Parent Added Successfully'); window.location='parent.php';</script>";
        }

    } else {

        /* UPDATE PARENT */
        $sql = "UPDATE parent SET 
        fname='$fname',
        lname='$lname',
        address='$address',
        gender='$gender',
        job='$occupation',
        contact='$contact',
        nic='$nic',
        email='$email'
        WHERE pid='$pid'";

        if ($conn->query($sql)) {
            echo "<script>alert('Parent Updated Successfully'); window.location='parent.php';</script>";
        }
    }
}

/* =========================
   PAGINATION LOGIC
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

/* TOTAL RECORDS */
$totalResult = $conn->query("SELECT COUNT(*) as total FROM parent");
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];

$totalPages = ceil($totalRecords / $limit);

/* FETCH PAGINATED DATA */
$sql = "SELECT * FROM parent LIMIT $start, $limit";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parent Management</title>
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
                        <h2><?= isset($_GET['update']) ? "Update Parent" : "Add Parent" ?></h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                        <form method="POST">

                            <div class="form-group">
                                <label>First Name</label>
                                <input name="fname" type="text" class="form-control" required value="<?= $fname ?>">
                            </div>

                            <div class="form-group">
                                <label>Last Name</label>
                                <input name="lname" type="text" class="form-control" required value="<?= $lname ?>">
                            </div>

                            <div class="form-group">
                                <label>National ID (NIC)</label>
                                <input name="nic" type="text" class="form-control" required value="<?= $nic ?>">
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
                                <input name="email" type="email" class="form-control" required value="<?= $email ?>">
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control"><?= $address ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Contact</label>
                                <input name="contact" type="text" class="form-control" required value="<?= $contact ?>">
                            </div>

                            <div class="form-group">
                                <label>Occupation</label>
                                <input name="job" type="text" class="form-control" required value="<?= $occupation ?>">
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary">
                                <?= isset($_GET['update']) ? "Update Parent" : "Add Parent" ?>
                            </button>

                        </form>

                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="col-md-9">

                <div class="x_panel">
                    <div class="x_title">
                        <h2>All Parents</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>NIC</th>
                                    <th>Gender</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Occupation</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['pid'] ?></td>
                                        <td><?= $row['fname'] ?> <?= $row['lname'] ?></td>
                                        <td><?= $row['nic'] ?></td>
                                        <td><?= $row['gender'] ?></td>
                                        <td><?= $row['address'] ?></td>
                                        <td><?= $row['contact'] ?></td>
                                        <td><?= $row['job'] ?></td>
                                        <td>
                                            <a href="parent.php?update=<?= $row['pid'] ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>

                        </table>

                        <!-- PAGINATION -->
                        <nav>
                            <ul class="pagination">

                                <!-- PREVIOUS -->
                                <li class="<?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a href="?page=<?= $page - 1 ?>">Previous</a>
                                </li>

                                <!-- PAGE NUMBERS -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="<?= ($page == $i) ? 'active' : '' ?>">
                                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- NEXT -->
                                <li class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a href="?page=<?= $page + 1 ?>">Next</a>
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