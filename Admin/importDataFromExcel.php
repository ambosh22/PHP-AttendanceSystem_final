<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';
require_once 'vendor/autoload.php';
$statusMsg = '';

function displayImportedData($data) {
    echo '<table class="table">';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . $cell . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

function saveImportedData($data) {
    global $conn;

    foreach ($data as $row) {
        $firstName = mysqli_real_escape_string($conn, $row[0]);
        $lastName = mysqli_real_escape_string($conn, $row[1]);
        $otherName = mysqli_real_escape_string($conn, $row[2]);
        $admissionNumber = mysqli_real_escape_string($conn, $row[3]);
        $classId = mysqli_real_escape_string($conn, $row[4]);
        $classArmId = mysqli_real_escape_string($conn, $row[5]);
        $dateCreated = date("Y-m-d");

        // Insert data into the database
        $insertQuery = "INSERT INTO tblstudents (firstName, lastName, otherName, admissionNumber, classId, classArmId, dateCreated) 
                        VALUES ('$firstName', '$lastName', '$otherName', '$admissionNumber', '$classId', '$classArmId', '$dateCreated')";
        $result = mysqli_query($conn, $insertQuery);

        if (!$result) {
            return false; // Return false if any insertion fails
        }
    }

    return true; // Return true if all insertions are successful
}

if (isset($_FILES['excelFile'])) {
    // Check if file is uploaded successfully
    if ($_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excelFile']['tmp_name'];
        $fileName = $_FILES['excelFile']['name'];

        // Check if file is an Excel file
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($fileType === 'xls' || $fileType === 'xlsx') {
            // Read Excel file using PHPExcel library (assuming it's installed)
            require_once 'PHPExcel/PHPExcel.php';

            $excelReader = PHPExcel_IOFactory::createReaderForFile($fileTmpPath);
            $excelObj = $excelReader->load($fileTmpPath);
            $worksheet = $excelObj->getActiveSheet();
            $excelData = $worksheet->toArray(null, true, true, true);

            // Display the imported data in a table for review
            displayImportedData($excelData);

            // Provide an option to save the imported data to the database
            echo '<form method="post">';
            echo '<button type="submit" name="saveImport" class="btn btn-primary">Save Data</button>';
            echo '</form>';

            // Handle saving the imported data
            if (isset($_POST['saveImport'])) {
                // Save imported data to the database
                $saveResult = saveImportedData($excelData);

                if ($saveResult) {
                    $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Data imported and saved successfully!</div>";
                } else {
                    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Error saving imported data!</div>";
                }
            }
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Please upload an Excel file (XLS or XLSX)!</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>File upload error!</div>";
    }
}
?>
