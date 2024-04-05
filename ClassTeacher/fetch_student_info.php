<?php
// Include your database connection file
include '../Includes/dbcon.php';

// Get the selected student ID from the AJAX request
$studentId = $_POST['student_id'];

// Query to fetch student information based on the selected student's ID
$query = "SELECT tblstudents.*, tblclass.className, tblclassarms.classArmName 
          FROM tblstudents 
          INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
          INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId 
          WHERE tblstudents.admissionNumber = '$studentId'";

// Execute the query
$result = $conn->query($query);

// Check if there is a result
if ($result->num_rows > 0) {
    // Fetch student details
    $student = $result->fetch_assoc();

    // Query to fetch attendance history of the selected student
    $attendanceQuery = "SELECT * FROM tblattendance WHERE admissionNo = '$studentId'";
    $attendanceResult = $conn->query($attendanceQuery);

    // Initialize an array to store attendance history
    $attendanceHistory = [];

    // Check if there is attendance history
    if ($attendanceResult->num_rows > 0) {
        // Fetch attendance history
        while ($row = $attendanceResult->fetch_assoc()) {
            $attendanceHistory[] = $row;
        }
    }

    // Add attendance history to the student details array
    $student['attendanceHistory'] = $attendanceHistory;

    // Return student details with attendance history as JSON response
    echo json_encode($student);
} else {
    // If no student found, return an empty response
    echo json_encode([]);
}
?>
