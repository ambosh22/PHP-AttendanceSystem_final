<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to fetch attendance details based on selected student and date
function fetchAttendanceDetails($studentId, $dateTaken, $conn) {
    // Prepare the SQL query to fetch attendance details
    $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
              tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
              tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber
              FROM tblattendance
              INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
              INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
              INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
              INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
              INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
              WHERE tblattendance.admissionNo = '$studentId' AND tblattendance.dateTimeTaken = '$dateTaken'";
    
    // Execute the query
    $result = $conn->query($query);
    
    // Initialize an array to store attendance data
    $attendanceData = array();
    
    // Check if there are any rows returned by the query
    if ($result->num_rows > 0) {
        // Loop through each row of the result set
        while ($row = $result->fetch_assoc()) {
            // Append each row to the attendance data array
            $attendanceData[] = $row;
        }
    }
    
    // Return the fetched attendance data
    return $attendanceData;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve selected student's ID and date from the form submission
    $studentId = $_POST['student_id'];
    $dateTaken = $_POST['dateTaken'];
    
    // Fetch attendance details based on the selected student and date
    $attendanceData = fetchAttendanceDetails($studentId, $dateTaken, $conn);
    
    // Store attendance data in session for use in the table
    $_SESSION['attendanceData'] = $attendanceData;
} else {
    // If form is not submitted, initialize attendance data in session
    $_SESSION['attendanceData'] = array();
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
                        <h1 class="h3 mb-0 text-gray-800">View Class Attendance</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Class Attendance</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Form Basic -->
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">View Class Attendance</h6>
                                </div>
                                <div class="card-body">
                                    <form id="attendanceForm" method="POST">
                                        <div class="form-group">
                                            <label class="form-control-label">Select Student<span class="text-danger ml-2">*</span></label>
                                            <select class="form-control" name="student_id" id="studentId">
                                                <?php
                                                // Fetch students assigned to the logged-in teacher
                                                $teacher_id = $_SESSION['userId']; // Assuming the teacher's ID is stored in 'userId'
                                                $query_assigned_students = "SELECT * FROM tblstudents WHERE teacherId = '$teacher_id'";
                                                $result_assigned_students = $conn->query($query_assigned_students);

                                                if ($result_assigned_students->num_rows > 0) {
                                                    while ($row = $result_assigned_students->fetch_assoc()) {
                                                        echo "<option value='{$row['admissionNumber']}'>{$row['firstName']} {$row['lastName']}</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No Students Assigned</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">View Attendance</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Attendance Table -->
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table align-items-center table-flush table-hover" id="attendanceTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Student No</th>
<th>Class</th>
<th>Class Section</th>
<th>Date</th>
<th>Status</th> <!-- New header for status -->
</tr>
</thead>
<tbody id="attendanceTableBody">
<?php
// Check if attendance data is available
if (!empty($_SESSION['attendanceData'])) {
    // Loop through each attendance record and display it in the table
    foreach ($_SESSION['attendanceData'] as $attendance) {
        echo "<tr>";
        echo "<td>{$attendance['firstName']}</td>";
        echo "<td>{$attendance['lastName']}</td>";
        echo "<td>{$attendance['admissionNumber']}</td>";
        echo "<td>{$attendance['className']}</td>";
        echo "<td>{$attendance['classArmName']}</td>";
        echo "<td>{$attendance['dateTimeTaken']}</td>";
        echo "<td>{$attendance['status']}</td>"; // Displaying status
        echo "</tr>";
    }
} else {
    // If no attendance data available, display a message
    echo "<tr><td colspan='7'>No attendance data available.</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>
<!-- End Attendance Table -->
</div>
</div>
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
<!-- Page level plugins -->
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script>
// Add event listener to form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('attendanceForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form submission
        fetchAndDisplayStudentInfo();
    });
});

// Function to fetch and display student information
function fetchAndDisplayStudentInfo() {
    var studentId = document.getElementById('studentId').value; // Get selected student's ID
    var dateTaken = new Date().toISOString().slice(0, 10); // Get today's date in YYYY-MM-DD format

    // Check if a student is selected
    if (!studentId) {
        alert("Please select a student.");
        return;
    }

    // Send AJAX request to fetch student details and attendance history
    fetch('fetch_student_info.php', {
        method: 'POST',
        body: new FormData(document.getElementById('attendanceForm'))
    })
    .then(response => response.json())
    .then(student => {
        // Display student details in table
        var attendanceTableBody = document.getElementById('attendanceTableBody');
        attendanceTableBody.innerHTML = ''; // Clear previous data

        // Append student details to table
        var studentRow = `<tr>
            <td>${student.firstName}</td>
            <td>${student.lastName}</td>
            <td>${student.admissionNumber}</td>
            <td>${student.className}</td>
            <td>${student.classArmName}</td>
            <td>${dateTaken}</td>
            <td>Present</td> <!-- Default to 'Present' -->
        </tr>`;
        attendanceTableBody.innerHTML = studentRow; // Set row to table body

        // Check if the student has attendance history
        if (student.attendanceHistory && student.attendanceHistory.length > 0) {
            // Append attendance history to table
            student.attendanceHistory.forEach(attendance => {
                if (attendance.status === '1') { // Display only if status is 'Present'
                    var attendanceRow = `<tr>
                        <td>${student.firstName}</td>
                        <td>${student.lastName}</td>
                        <td>${student.admissionNumber}</td>
                        <td>${student.className}</td>
                        <td>${student.classArmName}</td>
                        <td>${attendance.dateTimeTaken}</td>
                        <td>Present</td> <!-- Assume status is 'Present' -->
                    </tr>`;
                    attendanceTableBody.innerHTML += attendanceRow; // Append row to table body
                }
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

</script>
</body>
</html>

