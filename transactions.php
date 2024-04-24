<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <title>Transactions Page</title>
    <link rel="stylesheet" href="transaction.css">
</head>

<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <img src="logo.png" alt="Logo" />
            </div>
            <div class="btn-con">
                <a href="customers.php">Back</a>

                <button onclick="printBill()">Print / Save</button>
            </div>
        </div>
    </header>
    <?php

    // Include your database connection code here (similar to details.php)
    include('config.php');

    // Establish a database connection using the constants from config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if customer_id is set in the URL
    if (isset($_GET['id']) && isset($_GET['name'])) {
        $customer_id = $_GET['id'];
        $customer_name = $_GET['name'];

        // Fetch transactions for the specified customer_id
        $transactions_sql = "SELECT * FROM transactions WHERE customer_id = $customer_id";
        $transactions_result = $conn->query($transactions_sql);

        echo "<h2>Transactions for: $customer_name</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Original Due Amount</th><th>Paid Amount</th><th>Updated Due Amount</th><th>Payment Date</th></tr>";

        if ($transactions_result) {
            if ($transactions_result->num_rows > 0) {
                while ($row = $transactions_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['original_due_amount'] . "</td>";
                    echo "<td>" . $row['paid_amount'] . "</td>";
                    echo "<td>" . $row['updated_due_amount'] . "</td>";
                    echo "<td>" . $row['payment_date'] . "</td>";
                    echo "</tr>";
                }
            } else {
                // Display a message when no transactions are found
                echo "<tr><td colspan='5'>No transactions found for: $customer_name</td></tr>";
            }
        } else {
            // Display an error message if there's an issue with the query
            echo "<tr><td colspan='5'>Error fetching transactions: " . $conn->error . "</td></tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No customer_id specified in the URL.</p>";
    }

    $conn->close();
    ?>
    <script>
        function printBill() {
            window.print();
        }
    </script>
</body>

</html>