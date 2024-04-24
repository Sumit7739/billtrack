<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config.php');

// Establish a database connection using the constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check if email and OTP are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // Check if the email and OTP match in the users table
    $sql = "SELECT * FROM users WHERE email = '$email' AND otp = '$otp'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        // OTP is correct, generate a token
        $token = bin2hex(random_bytes(16));

        // Store the token in the database along with the user's email
        $sql = "UPDATE users SET token = '$token' WHERE email = '$email'";
        if ($conn->query($sql) === TRUE) {
            // Forward to another page with the email and token in the URL
            header("Location: forgot.php?email=$email&token=$token");
            exit();
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    } else {
        echo '<p class="error-message">Invalid OTP or email.</p>';
    }
}
$conn->close();
