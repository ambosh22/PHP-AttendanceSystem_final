<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Retrieve assigned students based on teacher's ID
$query_assigned_students = "SELECT tblstudents.*, tblclass.className, tblclassarms.classArmName 
                            FROM tblstudents 
                            INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
                            INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId 
                            WHERE tblstudents.teacherId = '$_SESSION[userId]'";
$result_assigned_students = $conn->query($query_assigned_students);

// Check if there are assigned students
if ($result_assigned_students->num_rows > 0) {
    // Array to hold assigned student data
    $assigned_students = array();
    while ($row = $result_assigned_students->fetch_assoc()) {
        $assigned_students[] = $row;
    }
}

// Count distinct classes and class sections from assigned students
$query_classes_count = "SELECT COUNT(DISTINCT classId) AS classesCount FROM tblstudents WHERE teacherId = '$_SESSION[userId]'";
$result_classes_count = $conn->query($query_classes_count);
$classes_count_row = $result_classes_count->fetch_assoc();
$classes_count = isset($classes_count_row['classesCount']) ? $classes_count_row['classesCount'] : 0;

$query_class_sections_count = "SELECT COUNT(DISTINCT classArmId) AS classSectionsCount FROM tblstudents WHERE teacherId = '$_SESSION[userId]'";
$result_class_sections_count = $conn->query($query_class_sections_count);
$class_sections_count_row = $result_class_sections_count->fetch_assoc();
$class_sections_count = isset($class_sections_count_row['classSectionsCount']) ? $class_sections_count_row['classSectionsCount'] : 0;

// Assuming $total_attendance is defined elsewhere in your code
$total_attendance = 0; // You need to define this variable properly

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
  <title>Dashboard</title>
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
            <h1 class="h3 mb-0 text-gray-800">Class Teacher Dashboard </h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <!-- Display Assigned Students -->
          <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Students</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($assigned_students);?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <!-- End Display Assigned Students -->

          <!-- Other Statistics -->
            <div class="col-xl-3 col-md-6">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Classes</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $classes_count; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard fa-2x text-primary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Class Sections</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $class_sections_count; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-code-branch fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Student Attendance</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_attendance; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar fa-2x text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Other Statistics -->

        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <?php include 'includes/footer.php';?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>  
</body>

</html>

