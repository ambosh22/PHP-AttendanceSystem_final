<?php
// Include necessary files and establish database connection
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Check if the form data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve selected student's ID and date from the form submission
    $studentId = $_POST['student_id'];
    
    // Prepare SQL statement with placeholders to prevent SQL injection
    $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
              tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
              tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber
              FROM tblattendance
              INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
              INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
              INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
              INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
              INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
              WHERE tblattendance.admissionNo = ?";

    // Prepare and bind parameters
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $studentId);

    // Execute query
    $stmt->execute();

    // Get result set
    $result = $stmt->get_result();

    $attendanceData = array(); // Initialize attendance data array

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Append each attendance record to the array
            $attendanceData[] = $row;
        }
    }

    // Send attendance data in JSON format
    echo json_encode($attendanceData);
} else {
    // If form data is not received, return empty response
    echo json_encode([]);
}
?>
