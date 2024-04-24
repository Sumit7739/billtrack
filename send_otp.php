<?php


session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// require 'PHPMailer-master/src/PHPMailer.php';
// require 'PHPMailer-master/src/SMTP.php';
// require 'PHPMailer-master/src/Exception.php';

require '/opt/lampp/htdocs/TCS/PHPMailer-master/src/PHPMailer.php';
require '/opt/lampp/htdocs/TCS/PHPMailer-master/src/SMTP.php';
require '/opt/lampp/htdocs/TCS/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_POST['email']; // Retrieve the entered email

    include('config.php');

    // Establish a database connection using the constants from config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    // Check if the email is present in the database and has verification status 0
    $sql = "SELECT * FROM users WHERE email = ? AND verification_status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User email found and verification status is 0

        // Generate a random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Update the database with the new OTP
        $updateSql = "UPDATE users SET otp = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $otp, $userEmail);
        if ($updateStmt->execute()) {
            // OTP updated successfully

            // Send the OTP to the entered email

            $mail = new PHPMailer();

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->Username = 'srisinhasumit10@gmail.com'; // Your email address
            $mail->Password = 'ggtbuofjfdmqcohr'; // Your email password

            $mail->setFrom('your@gmail.com', 'Tirupati Courier Service'); // Sender email and name

            $mail->addAddress($userEmail); // Recipient's email

            $mail->isHTML(true);
            $mail->Subject = 'OTP Verification';
            $mail->Body = '<h2>Your OTP for Password Resetting is: </h2>' . $otp;

            if ($mail->send()) {
                // OTP sent successfully
                $_SESSION['email'] = $userEmail; // Store the email in the session for further verification
                header('Location: otp_verification_2.php?email=' . $userEmail); // Redirect to OTP verification page
                exit();
            } else {
                $error = 'Error sending email: ' . $mail->ErrorInfo;
            }
        } else {
            $error = 'Error updating OTP: ' . $conn->error;
        }

        // Close the update statement
        $updateStmt->close();
    } else {
        // User not found or verification status is not 0
        $error = "Email Not verified.";
    }

    // Close database connections and statements
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6844614952438364" crossorigin="anonymous"></script>
    <title>Email Entry</title>
    <style>
        /* Reset some default styles for consistency */
        body,
        h1,
        h2,
        p,
        label,
        input,
        button {
            margin: 0;
            padding: 0;
        }

        /* Basic styling for the page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            position: absolute;
            top: 10%;
            width: 80%;
            height: 40%;
            text-align: center;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 30px;
            color: #303030;
            margin-bottom: 50px;
        }

        h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }

        input[type="email"] {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
        }

        button[type="submit"] {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s ease-in-out;
        }

        button[type="submit"]:hover {
            background-color: #357ae8;
        }

        /* Responsive design */
        @media screen and (max-width: 768px) {
            .container {
                width: 80%;
            }
        }
    </style>

</head>

<body>
    <div class="container">
        <h1>Forgot Your Password</h1>
        <h2>Enter Your Registered Email</h2>
        <?php if (isset($error)) : ?>
            <p class="error-msg">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <button type="submit">Send OTP</button>
        </form>
    </div>
</body>

</html>