<?php
include '../Includes/dbcon.php';
$excelData = json_decode($_POST['excelData'], true);

foreach ($excelData as $row) {
    $firstName = $row[0];
    $lastName = $row[1];
    $otherName = $row[2];
    $admissionNumber = $row[3];
    $classId = $row[4];
    $classArmId = $row[5];
    $dateCreated = date("Y-m-d");

    $query = "INSERT INTO tblstudents (firstName, lastName, otherName, admissionNumber, classId, classArmId, dateCreated) 
              VALUES ('$firstName', '$lastName', '$otherName', '$admissionNumber', '$classId', '$classArmId', '$dateCreated')";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $errorMessage = mysqli_error($conn);
        echo json_encode(['success' => false, 'message' => 'Error inserting data: ' . $errorMessage]);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);

header("Location: createStudents.php");
exit();
?>
