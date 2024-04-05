<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

function getStudentsAtRisk() {
    global $conn; 

    $query = "SELECT ta.admissionNo, ts.firstName, ts.lastName, tca.classArmName
          FROM tblattendance ta
          INNER JOIN tblstudents ts ON ts.admissionNumber = ta.admissionNo
          INNER JOIN tblclassArms tca ON tca.Id = ta.classArmId
          WHERE ta.status = '0'
          GROUP BY ta.admissionNo, ta.classArmId
          HAVING COUNT(*) >= 3";

    $result = $conn->query($query);

    $studentsAtRisk = array();

    while ($row = $result->fetch_assoc()) {
        $studentsAtRisk[] = $row;
    }

    return $studentsAtRisk;
}

$studentsAtRisk = getStudentsAtRisk();
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
        <?php include "Includes/sidebar.php"; ?>
        <!-- Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?php include "Includes/topbar.php"; ?>
                <!-- Topbar -->

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Student Attendance Report</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Class Attendance</li>
                        </ol>
                    </div>

                    <h1>Students At Risk</h1>

                    <?php
                    // Display the list of students at risk
                    if (!empty($studentsAtRisk)) {
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-bordered' id='dataTable' width='100%' cellspacing='0'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>First Name</th>";
                        echo "<th>Last Name</th>";
                        echo "<th>Student No</th>";
                        echo "<th>Class Section</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        foreach ($studentsAtRisk as $student) {
                            echo "<tr>";
                            echo "<td>{$student['firstName']}</td>";
                            echo "<td>{$student['lastName']}</td>";
                            echo "<td>{$student['admissionNo']}</td>";
                            echo "<td>{$student['classArmName']}</td>";
                            echo "</tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                    } else {
                        echo "<p>No students are currently at risk of dropping out due to three consecutive absences.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <!---Container Fluid-->
    </div>
    <!-- Footer -->
    <?php include "Includes/footer.php"; ?>
    <!-- Footer -->

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
</body>

</html>
