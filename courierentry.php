<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to validate and sanitize input data
function validateInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

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
    $customerId = validateInput($_POST["customer_id"]);

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

    // Check if the paid amount is greater than the total amount
    if ($paidAmount > $totalAmount) {
        echo "<h1>Error: Paid amount cannot be greater than the total amount. Redirecting in 5 seconds...</h1>";
        echo '<script>
                setTimeout(function(){
                    window.location.href = "dataentry.html";
                }, 5000);
              </script>';
        exit();
    }

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

    // Update customer_data table
    $updateCustomerSQL = "UPDATE customer_data SET total_amount = total_amount + $totalAmount, amount_paid = amount_paid + $paidAmount, due_amount = due_amount + $dueAmount WHERE id = $customerId";

    if ($conn->query($updateCustomerSQL) === TRUE) {
        // Close the database connection
        $conn->close();

        // Display a success message and redirect after a delay
        echo "<h1>Data inserted and updated successfully! Redirecting in 3 seconds...</h1>";
        echo '<script>
                setTimeout(function(){
                    window.location.href = "customers.php";
                }, 3000);
              </script>';
        exit();
    } else {
        // Handle the case where the update fails
        echo "<h1>Error updating Customer Data: " . $conn->error . "</h1>";
        header("refresh: 5; url=dataentry.html");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <link rel="stylesheet" href="courierentry.css" />
    <title>Courier Entry</title>
</head>

<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <img src="logo.png" alt="Logo" />
            </div>
        </div>
    </header>
    <div class="btn-con">
        <?php
        // Display customer name if available in the URL
        if (isset($_GET['customer_name'])) {
            $customerName = $_GET['customer_name'];
            echo "<h1>Name: $customerName</h1>";
        }
        ?>
    </div>
    <div class="btn-con1">
        <div class="btn">
            <a href="customers.php">Back</a>
        </div>
    </div>

    <div class="container">
        <div class="textinput">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <?php
                // Retrieve customer details from URL parameters
                $customer_id = $_GET['customer_id'];
                // $name = $_GET['name'];
                ?>
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />

                <div class="table-container">
                    <table id="data-table">
                        <tr>
                            <th>Amount</th>
                            <th>Weight</th>
                            <th>To Centre</th>
                            <th>A.W.B No</th>
                            <th>Date</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="amount[]" required /></td>
                            <td><input type="text" name="weight[]" required /></td>
                            <td><input type="text" name="to_centre[]" required /></td>
                            <td><input type="text" name="awb_no[]" required /></td>
                            <td><input type="date" name="entry_date[]" required /></td>
                        </tr>
                    </table>
                    <div class="end">
                        <!-- <hr /> -->
                        <button type="button" onclick="addRow()">Add Row</button>
                        <button type="button" onclick="removeRow()">Remove Row</button>

                        <button type="button" onclick="calculateTotal()">
                            Calculate Total
                        </button>
                        <div class="total-paid-due">
                            <label for="total_amount">Total Amount:</label>
                            <input type="text" id="total_amount" name="total_amount" readonly />

                            <label for="paid_amount">Paid Amount:</label>
                            <input type="text" id="paid_amount" name="paid_amount" oninput="updateDue()" required />

                            <label for="due_amount">Due Amount:</label>
                            <input type="text" id="due_amount" name="due_amount" readonly />

                            <label for="row_count">Number of Couriers:</label>
                            <input type="text" id="row_count" name="row_count" value="1" readonly />
                        </div>

                    </div>
                </div>
                <div class="in4">
                    <input type="submit" value="Submit" />
                </div>
            </form>
        </div>


        <script>
            function addRow() {
                var table = document.getElementById("data-table");
                var newRow = table.insertRow(table.rows.length);
                var cell1 = newRow.insertCell(0);
                var cell2 = newRow.insertCell(1);
                var cell3 = newRow.insertCell(2);
                var cell4 = newRow.insertCell(3);
                var cell5 = newRow.insertCell(4);

                cell1.innerHTML = '<input type="text" name="amount[]" required>';
                cell2.innerHTML = '<input type="text" name="weight[]" required>';
                cell3.innerHTML = '<input type="text" name="to_centre[]" required>';
                cell4.innerHTML = '<input type="text" name="awb_no[]" required>';
                cell5.innerHTML = '<input type="date" name="entry_date[]" required>';

                updateRowCount();
            }

            function removeRow() {
                var table = document.getElementById("data-table");
                var rowCount = table.rows.length;

                // Ensure there is at least one row before removing
                if (rowCount > 1) {
                    table.deleteRow(rowCount - 1);
                    updateRowCount();
                } else {
                    alert("Cannot remove the last row.");
                }
            }

            function calculateTotal() {
                var amounts = document.getElementsByName("amount[]");
                var totalAmount = 0;

                for (var i = 0; i < amounts.length; i++) {
                    totalAmount += parseFloat(amounts[i].value) || 0;
                }

                document.getElementById("total_amount").value =
                    totalAmount.toFixed(2);
                updateDue();
            }

            function updateRowCount() {
                var rowCount = document.getElementById("data-table").rows.length - 1;
                document.getElementById("row_count").value = rowCount;
            }

            function updateDue() {
                var totalAmount =
                    parseFloat(document.getElementById("total_amount").value) || 0;
                var paidAmount =
                    parseFloat(document.getElementById("paid_amount").value) || 0;

                var dueAmount = totalAmount - paidAmount;

                document.getElementById("due_amount").value = dueAmount.toFixed(2);
            }
        </script>
    </div>
</body>

</html>