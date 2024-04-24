<?php
// var_dump($_POST);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize customerId
$customerId = -1;

// Include your database connection code here (similar to details.php)
include('config.php');

// Establish a database connection using the constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $name = validateInput($_POST["name"]);
    $phone = validateInput($_POST["phone"]);
    $address = validateInput($_POST["address"]);

    // Retrieve data from the dynamic table
    $amounts = $_POST["amount"];
    $weights = $_POST["weight"];
    $toCentres = $_POST["to_centre"];
    $awbNos = $_POST["awb_no"];
    $entryDates = $_POST["entry_date"];

    // Retrieve other fields
    $totalAmount = $_POST["total_amount"];
    $paidAmount = $_POST["paid_amount"];
    $dueAmount = $_POST["due_amount"];
    $rowCount = $_POST["row_count"];

    // Check if the customer already exists in the database
    $sqlCheckCustomer = "SELECT * FROM customer_data WHERE name = ? AND phone_no = ?";
    $stmtCheckCustomer = $conn->prepare($sqlCheckCustomer);
    $stmtCheckCustomer->bind_param("ss", $name, $phone);
    $stmtCheckCustomer->execute();
    $resultCheckCustomer = $stmtCheckCustomer->get_result();

    if ($resultCheckCustomer->num_rows > 0) {
        // Customer with the same data already exists
        echo "<h1>Error: Customer with the same name and phone number already exists. Redirecting in 5 seconds...</h1>";
        echo '<script>
                setTimeout(function(){
                    window.location.href = "dataentry.html";
                }, 5000);
              </script>';
        exit();
    }
    // Check if the paid amount is greater than the total amount
    if ($paidAmount > $totalAmount) {
        echo "<h1>Error: Paid amount cannot be greater than the total amount. Redirecting in 5 seconds...</h1>";
        echo '<script>
            setTimeout(function(){
                window.location.href = "dataentry.html";
            }, 5000);
          </script>';
        exit();
    } else {
        // Customer does not exist, proceed with insertion
        $stmtCheckCustomer->close();

        // Example: Insert customer information using prepared statement
        $sqlCustomer = "INSERT INTO customer_data (name, phone_no, address, total_amount, due_amount, amount_paid) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtCustomer = $conn->prepare($sqlCustomer);
        $stmtCustomer->bind_param("ssssdd", $name, $phone, $address, $totalAmount, $dueAmount, $paidAmount);

        // Execute customer insert
        if ($stmtCustomer->execute()) {
            // Retrieve the auto-generated customer ID
            $customerId = $stmtCustomer->insert_id;

            // Example: Insert courier information using prepared statement
            $sqlCourier = "INSERT INTO courier_data (customer_id, total_sum, weight, to_centre, awb_no, entry_date, amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtCourier = $conn->prepare($sqlCourier);

            for ($i = 0; $i < $rowCount; $i++) {
                // Validate and sanitize input data for each row
                $amount = validateInput($amounts[$i]);
                $weight = validateInput($weights[$i]);
                $toCentre = validateInput($toCentres[$i]);
                $awbNo = validateInput($awbNos[$i]);
                $entryDate = validateInput($entryDates[$i]);

                $stmtCourier->bind_param("idssssd", $customerId, $totalAmount, $weight, $toCentre, $awbNo, $entryDate, $amount);
                // Execute courier insert for each row
                $stmtCourier->execute();
            }
            // Close the statements
            $stmtCourier->close();
            $stmtCustomer->close();

            // Close the database connection
            $conn->close();

            // Display a success message and redirect after a delay
            echo "<h1>Data inserted successfully! Redirecting in 3 seconds...</h1>";
            echo '<script>
                   setTimeout(function(){
                       window.location.href = "dataentry.html";
                   }, 3000);
                 </script>';
            exit();
        } else {
            echo "Error inserting customer data: " . $stmtCustomer->error;
        }
    }
} else {
    // Redirect back to the form if accessed directly without submitting
    header("Location: dataentry.html");
    exit();
}

// Function to validate and sanitize input data
function validateInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
