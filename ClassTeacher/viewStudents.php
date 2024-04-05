<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Debugging output
echo "Session UserID: ".$_SESSION['userId']."<br>";

$query = "SELECT tblclass.className, tblclassarms.classArmName 
    FROM tblclassteacher
    INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
    INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
    WHERE tblclassteacher.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();

// Retrieve the count of assigned students
$query_assigned_students_count = "SELECT COUNT(*) AS assignedStudentsCount FROM tblstudents WHERE teacherId = '$_SESSION[userId]'";
$result_assigned_students_count = $conn->query($query_assigned_students_count);
$assigned_students_count_row = $result_assigned_students_count->fetch_assoc();
$assigned_students_count = isset($assigned_students_count_row['assignedStudentsCount']) ? $assigned_students_count_row['assignedStudentsCount'] : 0;

// Function to fetch students grouped by class section and class year
function fetchStudentsGroupedByClassSection($conn, $teacherId) {
    $query_students = "SELECT tblstudents.Id, tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber, tblclass.className, tblclassarms.classArmName
                      FROM tblstudents
                      INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                      INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                      WHERE tblstudents.teacherId = '$teacherId'
                      ORDER BY tblclass.className, tblclassarms.classArmName";
    $result_students = $conn->query($query_students);
    
    $students_grouped = array();
    while ($student = $result_students->fetch_assoc()) {
        $class_year = $student['className'];
        $class_section = $student['classArmName'];
        $students_grouped[$class_year][$class_section][] = $student;
    }
    return $students_grouped;
}

// Fetch students grouped by class section
$students_grouped = fetchStudentsGroupedByClassSection($conn, $_SESSION['userId']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/Logo.png" rel="icon">
  <title>Students</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">All Students</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">All Students</li>
            </ol>
          </div>

          <!-- Student Tables --><h6 class="m-0 font-weight-bold text-danger">Note: <i>Click on the arrow to show/hide the table.</i></h6>
          <?php foreach ($students_grouped as $class_year => $class_sections): ?>
              <?php foreach ($class_sections as $class_section => $students): ?>
                  <div class="row">
                      <div class="col-lg-12">
                          <div class="card mb-4">
                              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                  <h6 class="m-0 font-weight-bold text-primary">Class <?php echo $class_year . ' - ' . $class_section; ?></h6>
                                  <!-- Down Arrow Icon -->
                                  <button class="btn btn-primary btn-toggle-table" data-target="#table-<?php echo $class_year . '-' . $class_section; ?>"><i class="fas fa-angle-down"></i></button>
                              </div>
                              <div class="table-responsive p-3" id="table-<?php echo $class_year . '-' . $class_section; ?>" style="display: none;">
                                  <table class="table align-items-center table-flush table-hover">
                                      <thead class="thead-light">
                                          <tr>
                                              <th>#</th>
                                              <th>First Name</th>
                                              <th>Last Name</th>
                                              <th>Student No</th>
                                              <th>Class</th>
                                              <th>Class Section</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php foreach ($students as $key => $student): ?>
                                              <tr>
                                                  <td><?php echo $key + 1; ?></td>
                                                  <td><?php echo $student['firstName']; ?></td>
                                                  <td><?php echo $student['lastName']; ?></td>
                                                  <td><?php echo $student['admissionNumber']; ?></td>
                                                  <td><?php echo $student['className']; ?></td>
                                                  <td><?php echo $student['classArmName']; ?></td>
                                              </tr>
                                          <?php endforeach; ?>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <?php include "Includes/footer.php";?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- JavaScript and jQuery -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>

  <!-- Toggle Table Script -->
  <script>
    $(document).ready(function() {
        $('.btn-toggle-table').click(function() {
            var targetId = $(this).data('target');
            $(targetId).toggle();
        });
    });
  </script>
</body>
</html>
