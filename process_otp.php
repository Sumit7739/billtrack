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
        // Update the verification status in the users table
        $sql = "UPDATE users SET verification_status = 1 WHERE email = '$email'";

        if ($conn->query($sql) === TRUE) {
            echo '<h1>OTP verified successfully! You Can Login Now</h1>';
            // echo '<h2'
            echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 3000);</script>';
            exit();
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    } else {
        echo '<p class="error-message">Invalid OTP or email.</p>';
    }
}
$conn->close();
