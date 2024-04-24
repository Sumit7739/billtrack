<?php
// var_dump($_POST);
// var_dump($_GET);
error_reporting(E_ALL);
ini_set('display_errors', 1);



if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];

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

    // Fetch customer's name and phone for the bill
    $customer_sql = "SELECT name, phone_no, amount_paid, due_amount, total_amount FROM customer_data WHERE id = $customer_id";
    $customer_result = $conn->query($customer_sql);

    if (!$customer_result) {
        die("Error executing the customer query: " . $conn->error);
    }

    // Check if any rows were returned before using fetch_assoc()
    if ($customer_result->num_rows > 0) {
        $customer_row = $customer_result->fetch_assoc();
        $customer_name = $customer_row['name'];
        $customer_phone = $customer_row['phone_no'];
        $totalPaidAmount = $customer_row['amount_paid'];
        $totalAmountDue = $customer_row['due_amount'];
        $totalSum = $customer_row['total_amount'];
    } else {
        // Handle the case where no customer data is found
        die("No customer data found for ID: $customer_id");
    }

    // Fetch courier data for the specified customer_id
    $courier_sql = "SELECT * FROM courier_data WHERE customer_id = $customer_id";
    $courier_result = $conn->query($courier_sql);

    if (!$courier_result) {
        die("Error executing the courier query: " . $conn->error);
    }

    // Initialize variables for total sum
    $allCourierData = array(); // New array to store all courier data

    // Iterate through each row in the result set
    while ($row = $courier_result->fetch_assoc()) {
        $allCourierData[] = $row; // Store the row in the array
        // $totalSum = $row['total_sum'];
    }

    // Close the database connection
    $conn->close();
} else {
    // Redirect to the main customers page if customer_id is not provided
    header("Location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <title>Generate Bill</title>
    <link rel="stylesheet" href="generate_bill.css"> <!-- Add your CSS file for styling if needed -->
</head>

<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <img src="logo2.png" alt="Logo" />
            </div>
        </div>
    </header>
    <!-- New section for company details -->
    <!-- <div class="company-details"> -->
        <!-- <h2>AMOD ENTERPRISE</h2> -->
        <!-- <p>Address: Nr. Amrit Jalpan, University Road, Sarai, BHAGALPUR(BR)-812002</p> -->
        <!-- <p>Contact: Mobile: 8051901635
            Mobile: 8051901637
            Email: amodjha32@gmail.com
        </p> -->
    <!-- </div> -->
    <div class="container">
        <div class="bill-container">
            <h2>
                Bill for:
                <?php echo $customer_name; ?> &nbsp;
                Mob:
                <?php echo $customer_phone; ?> &nbsp;
                Total Sum:
                <?php echo $totalSum; ?> &nbsp;
                Paid Amount:
                <?php echo $totalPaidAmount; ?> &nbsp;
                Due Amount:
                <?php echo $totalAmountDue; ?> &nbsp;
            </h2>

            <?php if (!empty($allCourierData)) : ?>
                <table id="bill-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Amount</th>
                            <th>Weight</th>
                            <th>To Centre</th>
                            <th>AWB No</th>
                            <th>Entry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allCourierData as $row) : ?>
                            <tr>
                                <td>
                                    <?php echo $row['id']; ?>
                                </td>
                                <td>
                                    <?php echo $row['amount']; ?>
                                </td>
                                <td>
                                    <?php echo $row['weight']; ?>
                                </td>
                                <td>
                                    <?php echo $row['to_centre']; ?>
                                </td>
                                <td>
                                    <?php echo $row['awb_no']; ?>
                                </td>
                                <td>
                                    <?php echo $row['entry_date']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No courier data found for the selected customer.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="button-container">
        <button onclick="printBill()">Print / Save</button>
    </div>


    <script>
        function printBill() {
            window.print();
        }
    </script>
</body>

</html>