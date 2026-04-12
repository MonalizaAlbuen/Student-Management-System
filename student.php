<?php session_start();

include_once 'database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Teacher') {
    header('Location:./logout.php');
    exit;
}

// ─── Auto-generate next SID ───────────────────────────────────────────────────
function generateSID($conn) {
    $result = $conn->query("SELECT MAX(CAST(sid AS UNSIGNED)) as max_sid FROM student");
    $row    = $result->fetch_assoc();
    $next   = ($row['max_sid'] ?? 0) + 1;
    return str_pad($next, 5, '0', STR_PAD_LEFT); // e.g. "00001"
}

// ─── Initial field values ─────────────────────────────────────────────────────
$sid       = "";
$fname     = "";
$lname     = "";
$classroom = "";
$dob       = "";
$gender    = "";
$address   = "";
$parent    = "";
$email     = "";

$isUpdate   = isset($_GET['update']);
$successMsg = "";
$errorMsg   = "";

// ─── Load student data for Update ────────────────────────────────────────────
if ($isUpdate) {
    $stmt = $conn->prepare("SELECT * FROM student WHERE sid = ?");
    $stmt->bind_param("s", $_GET['update']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $sid       = $row['sid'];
        $fname     = $row['fname'];
        $lname     = $row['lname'];
        $classroom = $row['classroom'];
        $email     = $row['email'];
        $dob       = date_format(new DateTime($row['bday']), 'Y-m-d');
        $gender    = $row['gender'];
        $address   = $row['address'];
        $parent    = $row['parent'];
    }
    $stmt->close();
}

// ─── Handle Form Submission ───────────────────────────────────────────────────
if (isset($_POST['submit'])) {
    $fname     = trim($_POST['fname']);
    $lname     = trim($_POST['lname']);
    $email     = trim($_POST['email']);
    $classroom = $_POST['classroom'];
    $dob       = date_format(new DateTime($_POST['dob']), 'Y-m-d');
    $gender    = $_POST['gender'];
    $address   = trim($_POST['address']);
    $parent    = !empty($_POST['parent']) ? $_POST['parent'] : 0;

    if ($isUpdate) {
        // Keep existing SID on update
        $sid  = $_POST['sid'];
        $stmt = $conn->prepare(
            "UPDATE student SET fname=?, lname=?, bday=?, address=?, gender=?, parent=?, classroom=?, email=? WHERE sid=?"
        );
        $stmt->bind_param("sssssssss", $fname, $lname, $dob, $address, $gender, $parent, $classroom, $email, $sid);

        if ($stmt->execute()) {
            $successMsg = "Student updated successfully! (ID: $sid)";
        } else {
            $errorMsg = "Database error: " . $conn->error;
        }
        $stmt->close();

    } else {
        // Auto-generate SID on insert
        $sid  = generateSID($conn);
        $stmt = $conn->prepare(
            "INSERT INTO student (sid, fname, lname, bday, address, gender, parent, classroom, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssssss", $sid, $fname, $lname, $dob, $address, $gender, $parent, $classroom, $email);

        if ($stmt->execute()) {
            $successMsg = "Student added successfully! (ID: $sid)";
            // Reset fields after successful add
            $sid = $fname = $lname = $classroom = $dob = $gender = $address = $parent = $email = "";
        } else {
            $errorMsg = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Dashboard</title>
  <link rel="icon" href="../img/favicon2.png">
  <?php include_once 'header.php'; ?>
</head>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">

      <!-- Sidebar -->
      <div class="col-md-3 left_col">
        <?php include_once 'sidebar.php'; ?>
      </div>

      <!-- Top Nav -->
      <?php include_once 'nav-menu.php'; ?>

      <!-- Page Content -->
      <div class="right_col" role="main">
        <div class="row">

          <!-- ── Left Panel: Add / Update Form ── -->
          <div class="col-md-3">
            <div class="x_panel">
              <div class="x_title">
                <h2><?php echo $isUpdate ? "Update Student" : "Add Student"; ?></h2>
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
              </div>

              <div class="x_content">

                <!-- Success Alert -->
                <?php if ($successMsg): ?>
                <div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <h4><i class="icon fa fa-check"></i> Success!</h4>
                  <?php echo htmlspecialchars($successMsg); ?>
                </div>
                <?php endif; ?>

                <!-- Error Alert -->
                <?php if ($errorMsg): ?>
                <div class="alert alert-danger alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <h4><i class="icon fa fa-warning"></i> Error!</h4>
                  <?php echo htmlspecialchars($errorMsg); ?>
                </div>
                <?php endif; ?>

                <form role="form" method="POST">

                  <div class="box-body">

                    <!-- Student ID: auto-generated (read-only) -->
                    <?php if ($isUpdate): ?>
                      <!-- Pass existing SID via hidden input on update -->
                      <input type="hidden" name="sid" value="<?php echo htmlspecialchars($sid); ?>">
                      <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($sid); ?>" disabled>
                      </div>
                    <?php else: ?>
                      <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" class="form-control" value="Auto-generated" disabled placeholder="Will be assigned automatically">
                      </div>
                    <?php endif; ?>

                    <!-- First Name -->
                    <div class="form-group">
                      <label>First Name</label>
                      <input name="fname" type="text" class="form-control" required
                             value="<?php echo htmlspecialchars($fname); ?>">
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                      <label>Last Name</label>
                      <input name="lname" type="text" class="form-control" required
                             value="<?php echo htmlspecialchars($lname); ?>">
                    </div>

                    <!-- Date of Birth -->
                    <div class="form-group">
                      <label>Date of Birth</label>
                      <div class="input-group date">
                        <input type="date" name="dob" class="form-control pull-right" id="datepicker"
                               placeholder="Select Student's Date of Birth"
                               value="<?php echo htmlspecialchars($dob); ?>" required>
                      </div>
                    </div>

                    <!-- Gender -->
                    <div class="form-group">
                      <label>Gender</label>
                      <div class="radio">
                        <label>
                          <input type="radio" name="gender" value="Male"
                                 <?php if ($gender == 'Male') echo 'checked'; ?>> Male
                        </label>
                      </div>
                      <div class="radio">
                        <label>
                          <input type="radio" name="gender" value="Female"
                                 <?php if ($gender == 'Female') echo 'checked'; ?>> Female
                        </label>
                      </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                      <label>Email</label>
                      <input name="email" type="email" class="form-control" required
                             value="<?php echo htmlspecialchars($email); ?>">
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                      <label>Address</label>
                      <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>

                    <!-- Classroom -->
                    <div class="form-group">
                      <label>Class Room</label>
                      <select name="classroom" class="form-control select2 select2-hidden-accessible"
                              style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option value="">Select Class Room</option>
                        <?php
                        $sql    = "SELECT * FROM classroom";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($classroom == $row['hno']) ? 'selected="selected"' : '';
                                echo "<option value='" . htmlspecialchars($row['hno']) . "' $selected>"
                                   . htmlspecialchars($row['title']) . "_ID:" . htmlspecialchars($row['hno'])
                                   . "</option>";
                            }
                        }
                        ?>
                      </select>
                    </div>

                    <!-- Parent -->
                    <div class="form-group">
                      <label>Parent</label>
                      <select name="parent" class="form-control select2 select2-hidden-accessible"
                              style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option value="0">Select Parent</option>
                        <?php
                        $sql    = "SELECT * FROM parent";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($parent == $row['pid']) ? 'selected="selected"' : '';
                                echo "<option value='" . htmlspecialchars($row['pid']) . "' $selected>"
                                   . htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname'])
                                   . " - ID:" . htmlspecialchars($row['pid'])
                                   . "</option>";
                            }
                        }
                        ?>
                      </select>
                    </div>

                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" name="submit" value="submit" class="btn btn-primary">
                      <?php echo $isUpdate ? "Update Student" : "Add Student"; ?>
                    </button>
                  </div>

                </form>
              </div><!-- /.x_content -->
            </div><!-- /.x_panel -->
          </div><!-- /.col-md-3 -->

          <!-- ── Right Panel: Students Table ── -->
          <div class="col-md-9">
            <div class="x_panel">
              <div class="x_title">
                <h2>All <small>Students</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
              </div>

              <div class="x_content">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="card-box table-responsive">
                      <p class="text-muted font-13 m-b-30">School Management System</p>

                      <table id="datatable-buttons" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <th>SID</th>
                            <th>Name</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Address</th>
                            <th>Classroom</th>
                            <th>Parent</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sql    = "SELECT * FROM student";
                          $result = $conn->query($sql);
                          if ($result && $result->num_rows > 0) {
                              while ($row = $result->fetch_assoc()) {
                                  $highlight = ($isUpdate && $_GET['update'] == $row['sid']) ? 'class="info"' : '';
                                  echo "<tr $highlight>";
                                  echo "<td>" . htmlspecialchars($row['sid'])       . "</td>";
                                  echo "<td>" . htmlspecialchars($row['fname'] . ' ' . $row['lname']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['bday'])      . "</td>";
                                  echo "<td>" . htmlspecialchars($row['gender'])    . "</td>";
                                  echo "<td>" . htmlspecialchars($row['address'])   . "</td>";
                                  echo "<td>" . htmlspecialchars($row['classroom']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['parent'])    . "</td>";
                                  echo "<td>
                                          <a href='student.php?update=" . urlencode($row['sid']) . "'>
                                            <small class='btn btn-sm btn-primary'>Update</small>
                                          </a>
                                        </td>";
                                  echo "</tr>";
                              }
                          } else {
                              echo "<tr><td colspan='8' class='text-center'>No students found.</td></tr>";
                          }
                          ?>
                        </tbody>
                      </table>

                    </div>
                  </div>
                </div>
              </div><!-- /.x_content -->
            </div><!-- /.x_panel -->
          </div><!-- /.col-md-9 -->

        </div><!-- /.row -->
      </div><!-- /.right_col -->

      <!-- Footer -->
      <footer>
        <div class="pull-right">
          Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
        </div>
        <div class="clearfix"></div>
      </footer>

    </div><!-- /.main_container -->
  </div><!-- /.container body -->

  <?php include_once 'footer.php'; ?>
</body>
</html>