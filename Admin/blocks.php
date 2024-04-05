<?php
error_reporting(E_ALL); // Enable error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to assign teacher to a student
function assignTeacherToStudent($conn, $studentId, $teacherId) {
    // Check if the student is already assigned to the same teacher
    $check_query = "SELECT teacherId FROM tblstudents WHERE Id = '$studentId'";
    $check_result = $conn->query($check_query);
    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        if ($row['teacherId'] == $teacherId) {
            return "Student is already assigned to this teacher.";
        }
    }

    // If not already assigned to the same teacher, proceed with the update
    $query = "UPDATE tblstudents SET teacherId = '$teacherId' WHERE Id = '$studentId'";
    return $conn->query($query);
}

// Function to assign teacher to all students in a class/section
function assignTeacherToClass($conn, $classId, $sectionId, $teacherId) {
    $query = "UPDATE tblstudents SET teacherId = '$teacherId' WHERE classId = '$classId'";
    if ($sectionId != 0) {
        $query .= " AND classArmId = '$sectionId'";
    }
    return $conn->query($query);
}

// Check if the assign teacher form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['classId']) && isset($_POST['teacherId']) && isset($_POST['studentIds'])) {
    $classId = $_POST['classId'];
    $sectionId = isset($_POST['sectionId']) ? $_POST['sectionId'] : 0; // Default value if not set
    $teacherId = $_POST['teacherId'];
    $studentIds = $_POST['studentIds'];

    // Assign teacher to class/section for each student
    $success = true;
    $error_message = '';
    foreach ($studentIds as $studentId) {
        $result = assignTeacherToStudent($conn, $studentId, $teacherId);
        if ($result !== true) {
            // Check if the result is not boolean (indicating an error message)
            $success = false;
            $error_message = $result; // Assign the error message
            break;
        }
    }

    // Check if all assignments were successful
    if ($success) {
        echo json_encode(array("success" => true, "message" => "Teacher assigned successfully to all students."));
        exit; // Stop further execution
    } else {
        echo json_encode(array("success" => false, "message" => "Failed to assign teacher to all students. Error: " . $error_message));
        exit; // Stop further execution
    }
}

// Fetch student data from the database
$query_students = "SELECT tblstudents.Id, tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber, tblclass.className, tblclassarms.classArmName
                  FROM tblstudents
                  INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                  INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                  ORDER BY tblclass.className, tblclassarms.classArmName";
$result_students = $conn->query($query_students);

// Check for errors
if (!$result_students) {
    die("Query failed: " . $conn->error); // Terminate script and display error message
}

// Group students by class and class section
$students_grouped = [];
while ($row = $result_students->fetch_assoc()) {
    $class_name = $row['className'];
    $class_section = $row['classArmName'];
    $students_grouped[$class_name][$class_section][] = $row;
}
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

          <!-- Student Tables -->
          <h6 class="m-0 font-weight-bold text-danger">Note: <i>Click on the arrow to show/hide the table.</i></h6>
          <?php
            foreach ($students_grouped as $class => $sections) {
              foreach ($sections as $section => $students) {
          ?>
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary"><?php echo $class . ' - ' . $section; ?></h6>
                  <button class="btn btn-primary btn-toggle-table" data-target="#table-<?php echo $class . '-' . $section; ?>"><i class="fas fa-angle-down"></i></button>
                </div>
                <div class="table-responsive p-3" id="table-<?php echo $class . '-' . $section; ?>" style="display: none;">
                  <h6 class="m-0 font-weight-bold">Teacher List</h6>
                  <div class="mb-3" style="width: 200px;">
                    <!-- Teacher Dropdown -->
                    <select class="form-control form-control-sm teacher-dropdown" data-class="<?php echo $class; ?>" data-section="<?php echo $section; ?>">
                      <option value="">Select Teacher</option>
                      <?php
                        $teacherQuery = "SELECT * FROM tblclassteacher";
                        $teacherResult = $conn->query($teacherQuery);
                        if ($teacherResult->num_rows > 0) {
                          while ($teacherRow = $teacherResult->fetch_assoc()) {
                            echo '<option value="' . $teacherRow['Id'] . '">' . $teacherRow['firstName'] . ' ' . $teacherRow['lastName'] . '</option>';
                          }
                        } else {
                          echo '<option value="">No teachers available</option>';
                        }
                      ?>
                    </select>
                    <!-- Assign Button -->
                    <button class="btn btn-success mb-3 assign-button">Assign</button>
                  </div>

                  <table class="table align-items-center table-flush table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Admission Number</th>
                        <th>Class</th>
                        <th>Class Section</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        foreach ($students as $key => $student) {
                          echo "<tr>";
                          echo "<td>" . $student['Id'] . "</td>";
                          echo "<td>" . $student['firstName'] . "</td>";
                          echo "<td>" . $student['lastName'] . "</td>";
                          echo "<td>" . $student['admissionNumber'] . "</td>";
                          echo "<td>" . $class . "</td>";
                          echo "<td>" . $section . "</td>";
                          echo "</tr>";
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <?php
              }
            }
          ?>
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

  <!-- Assign Teacher Script -->
  <script>
    $(document).ready(function() {
      $('.btn-toggle-table').click(function() {
        var targetId = $(this).data('target');
        $(targetId).toggle();
      });

      // Assign teacher to class/section
      $(".assign-button").click(function() {
        var classId = $(this).prev('.teacher-dropdown').data("class");
        var sectionId = $(this).prev('.teacher-dropdown').data("section");
        var teacherId = $(this).prev('.teacher-dropdown').val();
        
        // Collect student IDs from the table
        var studentIds = [];
        $(this).closest('.table-responsive').find('tbody tr').each(function() {
          studentIds.push($(this).find('td:first-child').text());
        });

        $.ajax({
          url: "blocks.php",
          type: "POST",
          data: {
            classId: classId,
            sectionId: sectionId,
            teacherId: teacherId,
            studentIds: studentIds
          },
          dataType: "json",
          success: function(response) {
            if (response.success) {
              alert(response.message);
              location.reload(); // Reload the page after successful assignment
            } else {
              if (response.message.includes("Student is already assigned")) {
                alert(response.message);
              } else {
                alert("Failed to assign teacher to all students. Error: " + response.message);
              }
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      });
    });
  </script>
</body>
</html>
