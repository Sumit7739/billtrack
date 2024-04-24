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



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  include('config.php');

  // Establish a database connection using the constants from config.php
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $token = bin2hex(random_bytes(16));

  // Check if the user already exists
  $sql = "SELECT * FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // User already exists
    $error = "User already exists";
  } else {
    // Insert the new user into the database
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
      // User created successfully
      $_SESSION['id'] = $stmt->insert_id;
      $stmt->close();

      // ... (your email verification code remains unchanged)
      // Function to generate a random 6-digit OTP
      function generateOTP()
      {
        $otp = "";
        for ($i = 0; $i < 6; $i++) {
          $otp .= mt_rand(0, 9);
        }
        return $otp;
      }

      // Retrieve the recipient email from the form
      $recipientEmail = $_POST['email'];

      // Generate OTP
      $otp = generateOTP();

      // Initialize PHPMailer
      $mail = new PHPMailer();

      // SMTP configuration
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->Port = 587;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->SMTPAuth = true;
      $mail->Username = 'srisinhasumit10@gmail.com'; // Your Gmail email address
      $mail->Password = 'ggtbuofjfdmqcohr'; // Your Gmail password

      // Sender and recipient
      $mail->setFrom('your@gmail.com', 'Tirupati Courier Service'); // Sender email and name
      $mail->addAddress($recipientEmail); // Recipient email

      // Save the OTP in the database
      $sql = "UPDATE users SET otp = '$otp' WHERE email = '$recipientEmail'";

      if ($conn->query($sql) === TRUE) {
        // Send email
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body = 'Your OTP for account verification is: ' . $otp;

        if ($mail->send()) {
          // Redirect to OTP verification page
          header('Location: otp_verification.php?email=' . $recipientEmail);
          exit();
        } else {
          $error = 'Error sending email: ' . $mail->ErrorInfo;
        }
      } else {
        $error = 'Error updating OTP: ' . $conn->error;
      }
    } else {
      // Failed to create user
      $error = "Failed to create user: " . $stmt->error;
    }
  }

  // $stmt->close(); // Close the statement
  // $conn->close(); // Close the database connection
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6844614952438364" crossorigin="anonymous"></script>
  <title>Signup</title>
  <link rel="stylesheet" href="stylesignup.css">
</head>

<body>
  <div class="nav">
    <header>
      <a href="index.html"><img src="logo.png" alt="Logo" /></a>
    </header>
  </div>
  <section>
    <div class="login-box">
      <div id="loaderOverlay">
        <div id="loader" class="loader"></div>
      </div>
      <form method="POST">
        <h2>Signup</h2>
        <?php if (isset($error)) { ?>
          <p class="error-msg">
            <?php echo $error; ?>
          </p>
        <?php } ?>
        <div class="input-box">
          <input type="text" id="name" name="name" required>
          <label for="name">Name</label>
        </div>
        <div class="input-box">
          <input type="email" id="email" name="email" required>
          <label for="email">Email</label>
        </div>
        <div class="input-box">
          <input type="password" id="password" name="password" required maxlength="8">
          <label for="password">Password</label>
        </div>
        <div class="input-box">
          <input type="password" id="confirmpassword" name="confirm password" required maxlength="8">
          <label for="confirmpassword">Confirm Password</label>
        </div>
        <p id="password-error" style="color: red;"></p>

        <button type="submit" id="submit" name="signup">Signup</button>

        <div class="log">
          <h4>Already have an account?
            <a href="login.php">SignIn</a>
          </h4>
        </div>
      </form>
    </div>
  </section>
  <script>
    // Get references to the password and confirm password input fields
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmpassword");

    // Get references to the error message element and submit button
    const passwordError = document.getElementById("password-error");
    const submitButton = document.getElementById("submit");

    // Add an input event listener to the confirm password field
    confirmPasswordInput.addEventListener("input", function() {
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Compare the passwords
      if (password === confirmPassword) {
        // Passwords match, clear the error message
        passwordError.textContent = "";
        submitButton.disabled = false; // Enable the submit button
      } else {
        // Passwords don't match, display an error message
        passwordError.textContent = "Passwords do not match!";
        submitButton.disabled = true; // Disable the submit button
      }
    });

    const submit = document.getElementById('submit');
    const emailField = document.getElementById('email');
    const loaderOverlay = document.getElementById('loaderOverlay');

    submit.addEventListener('click', function() {
      const emailValue = emailField.value.trim();

      if (emailField.checkValidity()) {
        loaderOverlay.style.display = 'block'; // Show overlay

        // Simulate asynchronous task (e.g., AJAX request)
        setTimeout(function() {}, 2000); // Simulated delay of 2 seconds
      } else {
        emailField.reportValidity();
      }
    });
  </script>
</body>

</html>