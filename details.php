<?php
include('config.php');

// Establish a database connection using the constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if customer_id and name are provided in the URL
if (isset($_GET['id']) && isset($_GET['name'])) {
    $customer_id = $_GET['id'];
    $customer_name = $_GET['name'];



    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $customer_sql = "SELECT * FROM customer_data WHERE id = $customer_id";
    $customer_result = $conn->query($customer_sql);

    if (!$customer_result) {
        die("Error executing the customer query: " . $conn->error);
    }

    $customer_row = $customer_result->fetch_assoc();
    $customer_name = $customer_row['name'];
    $totalPaidAmount = $customer_row['amount_paid'];
    $totalAmountDue = $customer_row['due_amount'];
    $totalSum = $customer_row['total_amount'];
    // Fetch courier data for the specified customer_id
    $courier_sql = "SELECT * FROM courier_data WHERE customer_id = $customer_id";
    $courier_result = $conn->query($courier_sql);

    if (!$courier_result) {
        die("Error executing the courier query: " . $conn->error);
    }

    // Close the database connection
    $conn->close();
} else {
    // Redirect to the main customers page if customer_id or name is not provided
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
    <title>Customer Details</title>
    <link rel="stylesheet" href="details.css">
</head>

<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <img src="logo.png" alt="Logo" />
            </div>
        </div>
    </header>

    <div class="container">
        <div class="table-container">
            <?php
            // Initialize variables for total paid amount and total amount due
            $totalPaidAmount = 0;
            $totalAmountDue = 0;

            // Iterate through each row in the result set
            while ($row = $courier_result->fetch_assoc()) {
                $totalPaidAmount = $customer_row['amount_paid'];
                $totalAmountDue = $customer_row['due_amount'];
                $totalSum = $customer_row['total_amount'];
            }
            ?>

            <div class="btn-con">
                <h2>Name:
                    <?php echo $customer_name; ?>
                </h2>
                <h2>ID:
                    <?php echo $customer_id; ?>
                </h2>
                <a href="bill.php?id=<?php echo $customer_id; ?>" class="generate-bill-btn" target="_blank">Generate Bill</a>

                <a href="transactions.php?id=<?php echo $customer_id; ?>&name=<?php echo urldecode($customer_name); ?>" class="generate-bill-btn">Payment History</a>

                <a href="courierentry.php?customer_id=<?php echo $customer_id; ?>&customer_name=<?php echo urlencode($customer_name); ?>" class="redirect-button">Add Entry</a>

                <a href="customers.php">Back</a>
            </div>

            <div class="btn-con1">
                <h2>Enter Paid Amount:</h2>
                <form action="update_payment.php" method="post">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

                    <label for="paid_amount">Paid Amount:</label>
                    <input type="text" id="paid_amount" name="paid_amount" required>

                    <label for="payment_date">Payment Date:</label>
                    <input type="date" id="payment_date" name="payment_date" required>

                    <button type="submit">Submit</button>
                </form>
            </div>
            <div class="btn-con2">
                <h3> <!-- Display Total Paid Amount and Total Amount Due here -->
                    <p>Total Amount:
                        <?php echo $totalSum; ?> rs
                    </p>
                    <p>Total Paid Amount:
                        <?php echo $totalPaidAmount; ?> rs
                    </p>
                    <p>Total Amount Due:
                        <?php echo $totalAmountDue; ?> rs
                    </p>
                </h3>
            </div>
            <?php if ($courier_result->num_rows > 0) : ?>
                <table id="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Weight</th>
                            <th>To Centre</th>
                            <th>AWB No</th>
                            <th>Entry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Reset the result set pointer to the beginning
                        $courier_result->data_seek(0);

                        // Iterate through each row again to display table rows
                        while ($row = $courier_result->fetch_assoc()) : ?>
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
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No courier data found for the selected customer.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>