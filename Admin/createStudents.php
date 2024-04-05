<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

//------------------------SAVE--------------------------------------------------

if(isset($_POST['save'])){
    
    $firstName=$_POST['firstName'];
    $lastName=$_POST['lastName'];
    $admissionNumber=$_POST['admissionNumber'];
    $classId=$_POST['classId'];
    $classArmId=$_POST['classArmId'];
    $dateCreated = date("Y-m-d");
    $teacherId = $_POST['teacherId']; // Added teacherId
    
    $query=mysqli_query($conn,"select * from tblstudents where admissionNumber ='$admissionNumber'");
    $ret=mysqli_fetch_array($query);

    if($ret > 0){ 
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>This Admission Number Already Exists!</div>";
    } else {
        $query=mysqli_query($conn,"insert into tblstudents(firstName,lastName,admissionNumber,password,classId,classArmId,dateCreated,teacherId) 
        values('$firstName','$lastName','$admissionNumber','12345','$classId','$classArmId','$dateCreated','$teacherId')");

        if ($query) {
            $statusMsg = "<div class='alert alert-success'  style='margin-right:700px;'>Student Created Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while creating student!</div>";
        }
    }
}

//--------------------EDIT------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn,"select * from tblstudents where Id ='$Id'");
    $row = mysqli_fetch_array($query);

    if(isset($_POST['update'])){
        $firstName=$_POST['firstName'];
        $lastName=$_POST['lastName'];
        $admissionNumber=$_POST['admissionNumber'];
        $classId=$_POST['classId'];
        $classArmId=$_POST['classArmId'];
        $teacherId = $_POST['teacherId']; // Added teacherId
        $dateCreated = date("Y-m-d");

        $query=mysqli_query($conn,"update tblstudents set firstName='$firstName', lastName='$lastName', admissionNumber='$admissionNumber',password='12345', classId='$classId',classArmId='$classArmId', teacherId='$teacherId'
            where Id='$Id'");
            
        if ($query) {
            echo "<script type = \"text/javascript\">
                window.location = (\"createStudents.php\")
                </script>"; 
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while updating student!</div>";
        }
    }
}

//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $classArmId = $_GET['classArmId'];

    $query = mysqli_query($conn,"DELETE FROM tblstudents WHERE Id='$Id'");

    if ($query == TRUE) {
        echo "<script type = \"text/javascript\">
        window.location = (\"createStudents.php\")
        </script>";
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while deleting student!</div>"; 
    }
}

  if (isset($_FILES['excelFile']['tmp_name'])) {
    $inputFileName = $_FILES['excelFile']['tmp_name'];
    
    // Load the Excel file
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

    // Get the first worksheet
    $worksheet = $spreadsheet->getActiveSheet();

    // Convert the worksheet data to array
    $excelData = $worksheet->toArray();

    // Assume the first row contains column headers
    $headers = array_shift($excelData);

    // Loop through data rows
    foreach ($excelData as $row) {
        // Process each row and insert into database
        // Ensure the structure of $row matches your database table columns
        // Insert data into database
    }

    // Update HTML table on the website
    // You can either reload the page or use AJAX to update the table dynamically
}
?>

<!DOCTYPE html>
<html lang="en">
<title>Create Student</title>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/Logo.png" rel="icon">
<?php include 'includes/title.php';?>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">



   <script>
    function classArmDropdown(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxClassArms2.php?cid="+str,true);
        xmlhttp.send();
    }
}
function updateFileName(input) {
            var fileName = input.files[0].name;
            document.getElementById('customFileLabel').innerText = fileName;
        }
</script>
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
            <h1 class="h3 mb-0 text-gray-800">Create Students</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Create Students</li>
            </ol>
            
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Create Students</h6>
                    <?php echo $statusMsg; ?>
                </div>
                <div class="card-body">
                  <form method="post">
                   <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        <label class="form-control-label">Firstname<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" name="firstName" value="<?php echo $row['firstName'];?>" id="exampleInputFirstName" >
                        </div>
                        <div class="col-xl-6">
                        <label class="form-control-label">Lastname<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="lastName" value="<?php echo $row['lastName'];?>" id="exampleInputFirstName" >
                        </div>
                    </div>
                     <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        
                        
                        <label class="form-control-label">Student Number<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" required name="admissionNumber" value="<?php echo $row['admissionNumber'];?>" id="exampleInputFirstName" >
                        </div>
                        <div class="col-xl-6">
    <label class="form-control-label">Teacher<span class="text-danger ml-2">*</span></label>
    <select required name="teacherId" class="form-control mb-3">
        <option value="">-- Select Teacher --</option>
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
</div>

                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        <label class="form-control-label">Select Class Yr<span class="text-danger ml-2">*</span></label>
                         <?php
                        $qry= "SELECT * FROM tblclass ORDER BY className ASC";
                        $result = $conn->query($qry);
                        $num = $result->num_rows;		
                        if ($num > 0){
                          echo ' <select required name="classId" onchange="classArmDropdown(this.value)" class="form-control mb-3">';
                          echo'<option value="">--Select Class Yr--</option>';
                          while ($rows = $result->fetch_assoc()){
                          echo'<option value="'.$rows['Id'].'" >'.$rows['className'].'</option>';
                              }
                                  echo '</select>';
                              }
                            ?>  
                        </div>
                        <div class="col-xl-6">
                        <label class="form-control-label">Class Section<span class="text-danger ml-2">*</span></label>
                            <?php
                                echo"<div id='txtHint'></div>";
                            ?>
                        </div>
                    </div>
                      <?php
                    if (isset($Id))
                    {
                    ?>
                    <button type="submit" name="update" class="btn btn-warning">Update</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                    } else {           
                    ?>
                    <button type="submit" name="save" class="btn btn-primary">Save</button>
                    <form method="post" enctype="multipart/form-data">
                    <div class="form-group row mb-3">
                    <div class="col-xl-6">
                  <div class="row">
   <div class="col-xl-9">
                    <label class="form-control-label">Upload Excel File<span class="text-danger ml-2">*</span></label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="excelFile" id="customFile" accept=".xls, .xlsx" onchange="updateFileName(this)">
                        <label class="custom-file-label" for="customFile" id="customFileLabel">Choose file</label>
                    </div>
                </div>
    
    <div class="col-xl-4 mt-4">
        <button type="button" id="importButton" class="btn btn-primary btn-xs">
            <i class="fas fa-file-excel mr-2 "></i>Import Excel
        </button>
    </div>
</div>



                    <?php
                    }         
                    ?>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
                 <div class="row">
              <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Student</h6>
                </div>
                <div class="form-group row">
         <label for="filterSelect" class="col-sm-2 col-form-label text-right">Filter by Class:</label>
        <div class="col-sm-4">
        <select class="form-control" id="filterSelect" onchange="filterTable(this.value)">
    <option value="all">All Classes</option>
    <?php
    $distinctClassesQuery = "SELECT DISTINCT className FROM tblclass ORDER BY className ASC";
    $distinctClassesResult = $conn->query($distinctClassesQuery);
    while ($classRow = $distinctClassesResult->fetch_assoc()) {
        echo '<option value="' . $classRow['className'] . '">' . $classRow['className'] . '</option>';
    }
    ?>
</select>

           </div>
                 </div>
                <div class="table-responsive e-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Student No</th>
                        <th>Class Yr</th>
                        <th>Class Section</th>
                        <th>Date Created</th>
                         <th>Edit</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                
                    <tbody id="dataTableBody">

                  <?php
                      $query = "SELECT tblstudents.Id,tblclass.className,tblclassarms.classArmName,tblclassarms.Id AS classArmId,tblstudents.firstName,
                      tblstudents.lastName,tblstudents.admissionNumber,tblstudents.dateCreated
                      FROM tblstudents
                      INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                      INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn=0;
                      $status="";
                      if($num > 0)
                      { 
                        while ($rows = $rs->fetch_assoc())
                          {
                             $sn = $sn + 1;
                            echo"
                              <tr>
                                <td>".$sn."</td>
                                <td>".$rows['firstName']."</td>
                                <td>".$rows['lastName']."</td>
                                <td>".$rows['admissionNumber']."</td>
                                <td>".$rows['className']."</td>
                                <td>".$rows['classArmName']."</td>
                                 <td>".$rows['dateCreated']."</td>
                                <td><a href='?action=edit&Id=".$rows['Id']."'><i class='fas fa-fw fa-edit'></i></a></td>
                                <td><a href='?action=delete&Id=".$rows['Id']."'><i class='fas fa-fw fa-trash'></i></a></td>
                              </tr>";
                          }
                      }
                      else
                      {
                           echo   
                           "<div class='alert alert-danger' role='alert'>
                            No Record Found!
                            </div>";
                      }
                      
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
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
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable(); 
      $('#dataTableHover').DataTable(); 
    });
    function filterTable(selectedClass) {
    var table = $('#dataTableHover').DataTable();

    // Reset the search
    table.search('').draw();

    // Apply the new search based on the selected class
    if (selectedClass.trim().toLowerCase() !== "all") {
        table.column(4).search(selectedClass.trim().toLowerCase(), true, false).draw();
    } else {
        // If "All Classes" is selected, show all rows
        table.columns().search('').draw();
    }
}


document.getElementById('importButton').addEventListener('click', function () {
    var fileInput = document.getElementById('customFile');

    if (fileInput.files.length > 0) {
        var file = fileInput.files[0];

        var reader = new FileReader();

        reader.onload = function (e) {
            var data = new Uint8Array(e.target.result);
            var workbook = XLSX.read(data, { type: 'array' });

            // Assume the first sheet is the one you want to import
            var sheetName = workbook.SheetNames[0];
            var sheet = workbook.Sheets[sheetName];

            // Convert the sheet data to JSON
            var jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

            // Update the table with the imported data
            updateTable(jsonData);
        };

        reader.readAsArrayBuffer(file);
    } else {
        alert('Please select an Excel file to import.');
    }
});
function updateTable(data) {
    var tableBody = document.getElementById('dataTableBody');

    // Clear existing table rows
    tableBody.innerHTML = '';

    // Add new rows based on the imported data
    for (var i = 0; i < data.length; i++) {
        var row = tableBody.insertRow(i);

        // Add a cell for the row number
        var numberCell = row.insertCell(0);
        numberCell.innerHTML = i + 1;

        // Add cells for each column in the imported data
        for (var j = 0; j < data[i].length; j++) {
            var cell = row.insertCell(j + 1);
            cell.innerHTML = data[i][j];
        }

        // Add a cell for the "Date Created" (assuming it's the last column in your data)
        var dateCreatedCell = row.insertCell(data[i].length + 1);
        dateCreatedCell.innerHTML = formatDate(new Date()); // Assuming date is generated on import

        // Add edit and delete buttons
        var editCell = row.insertCell(data[i].length + 2);
        editCell.innerHTML = '<a href="#" onclick="editRow(' + i + ')"><i class="fas fa-fw fa-edit"></i></a>';

        var deleteCell = row.insertCell(data[i].length + 3);
        deleteCell.innerHTML = '<a href="#" onclick="deleteRow(' + i + ')"><i class="fas fa-fw fa-trash"></i></a>';
    }

    // After updating the table, send the imported data to the server
    sendImportedDataToServer(data);
}

function formatDate(date) {
    var day = date.getDate();
    var month = date.getMonth() + 1;
    var year = date.getFullYear();
    return year + '-' + month + '-' + day;
}


function formatDate(date) {
    var day = date.getDate();
    var month = date.getMonth() + 1;
    var year = date.getFullYear();
    return year + '-' + month + '-' + day;
}

function sendImportedDataToServer(data) {
    // Create a new FormData object
    var formData = new FormData();

    // Append the file data to the FormData object
    formData.append('excelData', JSON.stringify(data));

    // Use fetch to send the FormData to the server
    fetch('saveDataToDatabase.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Handle the response from the server
        console.log(data);
        if (data.success) {
            // No need to do anything here as the table is already updated
        } else {
            alert('An error occurred during data import.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during data import.');
    });
}



  </script>
</body>

</html>