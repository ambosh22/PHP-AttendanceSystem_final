<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Start or resume the session
session_start();

// Retrieve assigned students based on teacher's ID
$query_assigned_students = "SELECT tblstudents.*, tblclass.className, tblclassarms.classArmName 
                            FROM tblstudents 
                            INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
                            INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId 
                            WHERE tblstudents.teacherId = '$_SESSION[userId]'";
$result_assigned_students = $conn->query($query_assigned_students);

// Check if there are assigned students
if ($result_assigned_students->num_rows > 0) {
    // Array to hold assigned student data grouped by class and class section
    $assigned_students_by_class = array();
    while ($row = $result_assigned_students->fetch_assoc()) {
        $assigned_students_by_class[$row['className']][$row['classArmName']][] = $row;
    }
}

// Function to take attendance
function takeAttendance($checkedStudents, $classId, $classArmId, $conn) {
    $dateTaken = date("Y-m-d");
    $statusMsg = '';

    // Check if attendance has already been taken for today
    $checkAttendanceQuery = "SELECT * FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId' AND dateTimeTaken = '$dateTaken' AND status = '1'";
    $resultCheckAttendance = $conn->query($checkAttendanceQuery);

    if($resultCheckAttendance->num_rows > 0) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Attendance has already been taken for today!</div>";
    } else {
        // Insert attendance records for checked students
        foreach($checkedStudents as $studentAdmissionNo) {
            $insertAttendanceQuery = "INSERT INTO tblattendance (admissionNo, classId, classArmId, dateTimeTaken, status) VALUES ('$studentAdmissionNo', '$classId', '$classArmId', '$dateTaken', '1')";
            if($conn->query($insertAttendanceQuery)) {
                $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Attendance taken successfully!</div>";
            } else {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while updating attendance!</div>";
            }
        }
    }

    return $statusMsg;
}

// Logic to handle form submission for taking attendance
if(isset($_POST['save'])){
  if(isset($_POST['check']) && !empty($_POST['check'])) {
      $checkedStudents = $_POST['check'];
      $classId = $_POST['class_id'];
      $classArmId = $_POST['class_arm_id'];

      // Check if attendance has already been taken for today for this class and class arm
      $checkAttendanceQuery = "SELECT * FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId' AND dateTimeTaken = CURDATE() AND status = '1'";
      $resultCheckAttendance = $conn->query($checkAttendanceQuery);

      if($resultCheckAttendance->num_rows > 0) {
          $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Attendance has already been taken for today!</div>";
      } else {
          // Process attendance for checked students
          foreach($checkedStudents as $studentAdmissionNo) {
              $updateAttendanceQuery = "UPDATE tblattendance SET status = '1' WHERE admissionNo = '$studentAdmissionNo' AND dateTimeTaken = CURDATE()";
              if($conn->query($updateAttendanceQuery)) {
                  $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Attendance taken successfully!</div>";
              } else {
                  $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while updating attendance!</div>";
              }
          }
      }
  }
}

// Logic to handle printing attendance details of selected students
if(isset($_POST['print_attendance'])) {
    // Retrieve the selected students from the session variable
    $classId = $_POST['class_id'];
    $classArmId = $_POST['class_arm_id'];
    
    if(isset($_SESSION['selected_students'][$classId . '-' . $classArmId])) {
        $selectedStudents = $_SESSION['selected_students'][$classId . '-' . $classArmId];

        // Get the class and class section names for the header
        $classNameHeader = $_POST['class_name_header'];
        $classSectionNameHeader = $_POST['class_section_name_header'];

        // Query to retrieve attendance details for selected students only
        $query_attendance_details = "SELECT tblattendance.*, tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber 
                                    FROM tblattendance 
                                    INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo 
                                    WHERE tblattendance.dateTimeTaken = CURDATE() AND tblattendance.admissionNo IN ('" . implode("','", $selectedStudents) . "')";

        $result_attendance_details = $conn->query($query_attendance_details);

        if($result_attendance_details->num_rows > 0) {
            // Start building the table markup
            $table_content = "<table border='1'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Student No</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

            // Variables to count present and absent students
            $presentCount = 0;
            $absentCount = 0;

            // Loop through each attendance record and append it to the table content
            $cnt = 1;
            while ($row = $result_attendance_details->fetch_assoc()) {
                $status = ($row['status'] == '1') ? "Present" : "Absent";

                // Increment present or absent count accordingly
                if ($status == 'Present') {
                    $presentCount++;
                } else {
                    $absentCount++;
                }

                $table_content .= "
                    <tr>
                        <td>{$cnt}</td>
                        <td>{$row['firstName']}</td>
                        <td>{$row['lastName']}</td>
                        <td>{$row['admissionNumber']}</td>
                        <td>{$status}</td>
                        <td>{$row['dateTimeTaken']}</td>
                    </tr>";
                $cnt++;
            }

            // Add rows for present and absent count at the bottom of the table
            $table_content .= "
                <tr>
                    <td colspan='4'>Total Present:</td>
                    <td>{$presentCount}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan='4'>Total Absent:</td>
                    <td>{$absentCount}</td>
                    <td></td>
                </tr>";

            // Close the table
            // Close the table
            $table_content .= "</tbody></table>";

            // Output the table content
            echo $table_content;

            // Clear the session variable to avoid conflicts
            unset($_SESSION['selected_students'][$classId . '-' . $classArmId]);

            exit();
        }  else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>No attendance records found for the selected students!</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>No students selected for attendance!</div>";
    }
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
<title>Dashboard</title>
<link href="img/logo/Logo.png" rel="icon">
<link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/ruang-admin.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
<style>
  .toggle-table {
    display: none;
  }
  .datepicker {
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }
</style>
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
                <h1 class="h3 mb-0 text-gray-800">Take Attendance</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="./">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Student in Class</li>
                </ol>
            </div>

            <!-- Display status message -->
            <?php echo isset($statusMsg) ? $statusMsg : ''; ?>
            <h6 class="m-0 font-weight-bold text-danger">Note: <i>Click on the arrow to show/hide the table.</i></h6>

            <!-- Loop through each class and class section -->
            <?php if(isset($assigned_students_by_class) && !empty($assigned_students_by_class)): ?>
                <?php foreach ($assigned_students_by_class as $className => $classSections): ?>
                    <?php foreach ($classSections as $classSectionName => $students): ?>
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><?php echo $className . ' - ' . $classSectionName; ?></h6>
                                <button class="toggle-arrow btn btn-link" data-toggle-id="<?php echo strtolower(str_replace(' ', '-', $className . '-' . $classSectionName)); ?>"><i class="fas fa-angle-down fa-2x text-info"></i></button>
                            </div>
                            <div class="table-responsive p-3 toggle-table" id="<?php echo strtolower(str_replace(' ', '-', $className . '-' . $classSectionName)); ?>">
                              <input type="text" class="form-control datepicker" id="datepicker-<?php echo $students[0]['classId'] . '_' . $students[0]['classArmId']; ?>" placeholder="Select date" autocomplete="off">
                                      <form id="attendanceForm_<?php echo $students[0]['classId'] . '_' . $students[0]['classArmId']; ?>" method="post">
                                    <input type="hidden" name="class_id" value="<?php echo $students[0]['classId']; ?>">
                                    <input type="hidden" name="class_arm_id" value="<?php echo $students[0]['classArmId']; ?>">
                                    <!-- Add class name and class section as hidden inputs -->
                                    <input type="hidden" name="class_name_header" value="<?php echo $className; ?>">
                                    <input type="hidden" name="class_section_name_header" value="<?php echo $classSectionName; ?>">
                                    <table class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Student No</th>
                                                <th>Check</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $key => $student): ?>
                                                <tr>
                                                    <td><?php echo $key + 1; ?></td>
                                                    <td><?php echo $student['firstName']; ?></td>
                                                    <td><?php echo $student['lastName']; ?></td>
                                                    <td><?php echo $student['admissionNumber']; ?></td>
                                                    <td><input name='check[]' type='checkbox' value="<?php echo $student['admissionNumber']; ?>" class='form-control'></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                      
                                    </table>
                                    <br>
                                    <button type="button" class="btn btn-primary takeAttendanceBtn" data-form-id="<?php echo $students[0]['classId'] . '_' . $students[0]['classArmId']; ?>">Take Attendance</button>
                                    <!-- Print button to download attendance details -->
                                    <button type="button" class="btn btn-success printAttendanceBtn" data-class-id="<?php echo $students[0]['classId']; ?>" data-class-arm-id="<?php echo $students[0]['classArmId']; ?>" disabled>Print Attendance</button> 
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    No assigned students found.
                </div>
            <?php endif; ?>
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

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/ruang-admin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
   $(document).ready(function() {
        // Initialize date picker for each table
        $('.datepicker').each(function() {
            var tableId = $(this).closest('.toggle-table').attr('id');
            $(this).datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            }).css('width', '220px').on('changeDate', function() {
                updateCheckedCheckboxes(tableId);
            });
        });

        // Function to update checked checkboxes based on stored data
        function updateCheckedCheckboxes(tableId) {
            var selectedDate = $('#' + tableId + ' .datepicker').val();
            var storedData = JSON.parse(sessionStorage.getItem(selectedDate)) || {};
            $('#' + tableId + ' input[name="check[]"]').prop('checked', false); // Uncheck all checkboxes
            Object.keys(storedData).forEach(function(admissionNumber) {
                $('#' + tableId + ' input[name="check[]"][value="' + admissionNumber + '"]').prop('checked', true);
            });
        }

        // Handle checkbox change event
        $('input[name="check[]"]').change(function() {
            var tableId = $(this).closest('.toggle-table').attr('id');
            var selectedDate = $('#' + tableId + ' .datepicker').val();
            var checkedStudents = $('#' + tableId + ' input[name="check[]"]:checked').map(function() {
                return this.value;
            }).get();

            // Store checked student admission numbers in session storage for the selected date
            var storedData = checkedStudents.reduce(function(obj, item) {
                obj[item] = true;
                return obj;
            }, {});
            sessionStorage.setItem(selectedDate, JSON.stringify(storedData));
        });

        // Call the function to update checked checkboxes on page load
        $('.toggle-table').each(function() {
            var tableId = $(this).attr('id');
            updateCheckedCheckboxes(tableId);
        });
    });


    $(document).ready(function() {
        $('.toggle-arrow').click(function() {
            var toggleId = $(this).data('toggle-id');
            $('#' + toggleId).slideToggle();
        });

        // Handle taking attendance when Take Attendance button is clicked
        $('.takeAttendanceBtn').click(function() {
            var formId = $(this).data('form-id');
            var checkedStudents = $('#attendanceForm_' + formId).find('input[name="check[]"]:checked').map(function() {
                return this.value;
            }).get();

            // Store checked student admission numbers in sessionStorage
            sessionStorage.setItem('attendance_' + formId, JSON.stringify(checkedStudents));

            // Calculate present and absent counts
            var presentCount = $('#attendanceForm_' + formId).find('input[name="check[]"]:checked').length;
            var totalStudents = $('#attendanceForm_' + formId).find('input[name="check[]"]').length;
            var absentCount = totalStudents - presentCount;

            // Update present and absent counts in the table footer
            $('#presentCount_' + formId).text(presentCount);
            $('#absentCount_' + formId).text(absentCount);

            // Enable the Print Attendance button
            $('#attendanceForm_' + formId).find('.printAttendanceBtn').prop('disabled', false);
        });

        // Handle printing attendance when Print Attendance button is clicked
        $('.printAttendanceBtn').click(function() {
            var classId = $(this).data('class-id');
            var classArmId = $(this).data('class-arm-id');
            var storedAttendance = sessionStorage.getItem('attendance_' + classId + '_' + classArmId);
            if (storedAttendance) {
                var checkedStudents = JSON.parse(storedAttendance);
                var tableHtml = "<table border='1'><thead><tr><th colspan='6'>" + $('#attendanceForm_' + classId + '_' + classArmId).find('input[name="class_name_header"]').val() + " - " + $('#attendanceForm_' + classId + '_' + classArmId).find('input[name="class_section_name_header"]').val() + "</th></tr><tr><th>#</th><th>First Name</th><th>Last Name</th><th>Student No</th><th>Status</th><th>Date</th></tr></thead><tbody>";
                var cnt = 1;
                checkedStudents.forEach(function(admissionNumber) {
                    var studentRow = $('#attendanceForm_' + classId + '_' + classArmId).find('input[name="check[]"][value="' + admissionNumber + '"]').closest('tr');
                    tableHtml += "<tr>";
                    tableHtml += "<td>" + cnt + "</td>";
                    tableHtml += "<td>" + studentRow.find('td:eq(1)').text() + "</td>";
                    tableHtml += "<td>" + studentRow.find('td:eq(2)').text() + "</td>";
                    tableHtml += "<td>" + studentRow.find('td:eq(3)').text() + "</td>";
                    tableHtml += "<td>" + (studentRow.find('input[name="check[]"][value="' + admissionNumber + '"]').prop('checked') ? "Present" : "Absent") + "</td>";
                    tableHtml += "<td>" + $('#datepicker-' + classId + '_' + classArmId).val() + "</td>";
                    tableHtml += "</tr>";
                    cnt++;
                });
                tableHtml += "</tbody></table>";

                var newWindow = window.open();
                newWindow.document.write('<html><head><title>Attendance Details</title></head><body>');
                newWindow.document.write(tableHtml);
                newWindow.document.write('</body></html>');
                newWindow.document.close();
                newWindow.print();
            }
        });
    });
</script>

</body>
</html>


