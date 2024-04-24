<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize user input
    $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $paid_amount = filter_input(INPUT_POST, 'paid_amount', FILTER_VALIDATE_FLOAT);
    $payment_date = $_POST['payment_date'];

    if (!$customer_id || !$paid_amount) {
        echo "<h1>Invalid input</h1>";
        header("refresh: 3; url=details.php");
        exit();
    }

    // Include your database connection code here (similar to details.php)
    include('config.php');

    // Establish a database connection using the constants from config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for successful connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Section 1: Fetch the amount due for the customer
    $due_amount_sql = "SELECT due_amount FROM customer_data WHERE id = $customer_id";
    $due_amount_result = $conn->query($due_amount_sql);

    if ($due_amount_result) {
        $due_amount_row = $due_amount_result->fetch_assoc();
        $original_due_amount = $due_amount_row['due_amount'];

        // Ensure due amount doesn't go below zero
        if ($original_due_amount < $paid_amount) {
            echo "<h1>Error: Paid amount exceeds due amount</h1>";
            header("refresh: 3; url=details.php");
            exit();
        }

        // Ensure entered amount doesn't exceed due amount
        if ($paid_amount > $original_due_amount) {
            echo "<h1>Error: Paid amount exceeds due amount</h1>";
            header("refresh: 3; url=details.php");
            exit();
        }

        // Section 2: Update amount_paid, due_amount, and payment_date in the customer_data table
        $update_sql = "UPDATE customer_data SET amount_paid = amount_paid + $paid_amount, due_amount = due_amount - $paid_amount, last_payment_date = '$payment_date' WHERE id = $customer_id";

        if ($conn->query($update_sql) === TRUE) {
            // Calculate the updated due amount
            $updated_due_amount = $original_due_amount - $paid_amount;

            // Section 3: Insert transaction record with updated due amount
            $insert_transaction_sql = "INSERT INTO transactions (customer_id, original_due_amount, paid_amount, updated_due_amount, payment_date) VALUES ($customer_id, $original_due_amount, $paid_amount, $updated_due_amount, '$payment_date')";

            if ($conn->query($insert_transaction_sql) === TRUE) {
                echo "<h1>Payment and transaction updated successfully</h1>";
                header("refresh: 3; url=details.php");
                exit();
            } else {
                echo "<h1>Error inserting transaction record: " . $conn->error . "</h1>";
                header("refresh: 5; url=details.php");
                exit();
            }
        } else {
            echo "<h1>Error updating payment: " . $conn->error . "</h1>";
            header("refresh: 5; url=details.php");
            exit();
        }
    } else {
        echo "<h1>Error fetching due amount: " . $conn->error . "</h1>";
        header("refresh: 5; url=details.php");
        exit();
    }

    // $conn->close();
}
