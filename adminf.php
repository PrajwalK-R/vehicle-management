<?php
session_start();
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vsms";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql_vehicle = "SELECT DISTINCT vehicle_id FROM appointment";
$result_vehicle = $conn->query($sql_vehicle);
$vehicle_idOptions = "";
if ($result_vehicle->num_rows > 0) {
    while($row_vehicle = $result_vehicle->fetch_assoc()) {
        $vehicle_idOptions .= "<option value='".$row_vehicle['vehicle_id']."'>".$row_vehicle['vehicle_id']."</option>";
    }
} else {
    echo "0 results";
}
$sql_service = "SELECT DISTINCT service_type FROM appointment";
$result_service = $conn->query($sql_service);
$serviceOptions = "";
if ($result_service->num_rows > 0) {
    while($row_service = $result_service->fetch_assoc()) {
        $serviceOptions .= "<option value='".$row_service['service_type']."'>".$row_service['service_type']."</option>";
    }
} else {
    echo "0 results";
}
$sql_technician = "SELECT tech_id FROM technician";
$result_technician = $conn->query($sql_technician);
$techOptions = "";
if ($result_technician->num_rows > 0) {
    while($row_technician = $result_technician->fetch_assoc()) {
        $techOptions .= "<option value='".$row_technician['tech_id']."'>".$row_technician['tech_id']."</option>";
    }
} else {
    echo "0 results";
}
$sql_customer= "SELECT cust_id FROM customer";
$result_customer = $conn->query($sql_customer);
$cust_idOptions = "d[]";
if ($result_customer->num_rows > 0) {
    while($row_customer = $result_customer->fetch_assoc()) {
        $cust_idOptions .= "<option value='".$row_customer['cust_id']."'>".$row_customer['cust_id']."</option>";
    }
} else {
    echo "0 results";
}
$conn->close();
$showalert=false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $vehicle_id = $_POST['vehicle_id'];
    $description = $_POST['description'];
    $cost= $_POST['cost'];
    $tech_id = $_POST['tech_id'];
    $date=date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO service (vehicle_id, description, cost, date, tech_id) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error: " . $conn->error);
    }
    $stmt->bind_param("ssisi", $vehicle_id, $description, $cost, $date, $tech_id);
    if ($stmt->execute()) {
        $showalert=true;
    } 
    $service_id = $stmt->insert_id;
    
    $cust_id = $_POST['cust_id'];
    $stmt_payment = $conn->prepare("INSERT INTO payment (service_id,cost, cust_id) VALUES (?, ?, ?)");
    if (!$stmt_payment) {
        die("Error: " . $conn->error);
    }
    $stmt_payment->bind_param("iis", $service_id, $cost, $cust_id);
    if ($stmt_payment->execute()) {
         $showalert=true;
    } 
    $stmt->close();
    $stmt_payment->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background-color: #230D83; 
      }
      
      .container {
        background-color: #f0f8ff; 
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(5, 0, 0);
      }

      .container:hover,
      .btn-primary:hover {
        background-color:#CCCCFF; 
      }

      .form-control {
        border-color: #007bff; 
      }

      .form-control:focus {
        border-color: #0056b3; 
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25); 
      }
    </style>
</head>
<body>
<?php require 'partials/_nav.php' ?>
<?php
if($showalert){
      echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
              <strong>Success!</strong>application saved.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
    ?>
<div class="container mt-5">
    <h2>Enter Details</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="mb-3">
            <label for="vehicle_id" class="form-label">Vehicle ID</label>
            <select name="vehicle_id" id="vehicle_id" name="vehicle_id" class="form-select" required>
                <?php echo $vehicle_idOptions; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <select name="description" id="description" name="description" class="form-select" required>
                <?php echo $serviceOptions; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="cost" class="form-label">Amount</label>
            <input type="number" class="form-control" id="cost" name="cost" placeholder="Enter Amount" required>
        </div>
        <div class="mb-3">
            <label for="tech_id" class="form-label">Technician ID</label>
            <select name="tech_id" id="tech_id" name="tech_id" class="form-select" required>
                <?php echo $techOptions; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="cust_id" class="form-label">Customer ID</label>
            <select name="cust_id" id="cust_id" name="cust_id" class="form-select" required>
                <?php echo $cust_idOptions; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
