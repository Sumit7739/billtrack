<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if user is already authenticated, redirect to admin page
if (isset($_SESSION['id'])) {
  header("Location: dataentry.html");
  exit();
}

// Check if the form was submitted
if (isset($_POST['submit'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  include('config.php');

  // Establish a database connection using the constants from config.php
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Prepare and execute a SQL query to retrieve user data
  $sql = "SELECT * FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // User found, check the password
    $row = $result->fetch_assoc();
    $storedPassword = $row['password'];
    $verificationStatus = $row['verification_status'];

    if ($verificationStatus == 0) {
      // Redirect to a particular page when verification_status is 0
      header('Location: otp_verification.php?email=' . $email);
      exit();
    }

    // Verify the hashed password
    if (password_verify($password, $storedPassword)) {
      // Password is correct, login successful
      $_SESSION['id'] = $row['id'];
      $stmt->close();
      $conn->close();
      header("Location: home.html"); // Redirect to the success page
      exit();
    } else {
      // Invalid password
      $error = "Incorrect password";
    }
  } else {
    // User not found
    $error = "User not found";
  }

  $stmt->close(); // Close the statement
  $conn->close(); // Close the database connection
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
  <link rel="stylesheet" href="stylesignup.css">
  <title>Login</title>

</head>

<body>
  <div class="nav">
    <header>
      <a href="index.html"><img src="logo.png" alt="Logo" /></a>
    </header>
  </div>

  <section>
    <div class="login-box">
      <form method="POST">
        <h2>User Login</h2>
        <?php if (isset($error)) : ?>
          <p class="error-msg">
            <?php echo $error; ?>
          </p>
        <?php endif; ?>
        <div class="input-box">
          <input type="email" id="email" name="email" required>
          <label for="email">Enter Your Email</label>
        </div>
        <div class="input-box">
          <input type="password" id="password" name="password" required maxlength="8">
          <label for="password">Enter Your Password</label>
        </div>
        <div class="checkbox">
          <input type="checkbox"> Remember Me.
        </div>
        <div class="forpass">
          <a href="send_otp.php">forgot password?</a>
        </div>
        <button type="submit" name="submit">Login</button>
        </div>
      </form>
    </div>
  </section>
</body>

</html>