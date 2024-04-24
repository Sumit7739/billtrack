<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config.php');

// Establish a database connection using the constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the token is provided in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check the database for the user with this token
    $sql = "SELECT email, password FROM users WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $userEmail = $row['email'];

        // The user is identified by the email, and you can proceed with password change

        // Check if the form is submitted for password change
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
            $newPassword = $_POST['new_password'];

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $updateSql = "UPDATE users SET password = ? WHERE email = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $hashedPassword, $userEmail);

            if ($updateStmt->execute()) {
                // Password updated successfully
                echo '<h1>Password Changed successfully! You Can Login Now</h1>';
                echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 3000);</script>';
                exit();
            } else {
                echo 'Error updating password: ' . $conn->error;
            }
        }
    } else {
        // Invalid token, show an error message or redirect as needed
        echo "Invalid token. Please request a new password change link.";
        exit();
    }
} else {
    // Token not provided in the URL, show an error message or redirect as needed
    echo "Token not found in the URL.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6844614952438364" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <link rel="stylesheet" href="styles.css">
    <title>Password Change</title>
    <style>
        /* Reset some default styles for consistency */
        body,
        h1,
        h2,
        p,
        ul,
        li,
        input,
        button {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        /* Basic styling for the page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .container {
            position: absolute;
            left: 10%;
            top: 20%;
            width: 80%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        .password-form label {
            display: block;
            margin-top: 10px;
            font-size: 16px;
            color: #555;
        }

        .password-form input[type="password"] {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 5px;
        }

        .password-form input[type="text"] {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 5px;
        }

        .password-form button[type="submit"] {
            background-color: #4285f4;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        .password-form button[type="submit"]:hover {
            background-color: #357ae8;
        }

        /* Media Queries for responsiveness */
        @media screen and (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 10px;
            }

            .header h1 {
                font-size: 20px;
            }

            .password-form label,
            .password-form input[type="password"] {
                font-size: 14px;
            }

            .password-form button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Password Change</h1>
        </div>
        <?php if (isset($error)) : ?>
            <p class="error-msg">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>
        <form class="password-form" method="POST">
            <label for="new_password">New Password:</label>
            <input type="text" id="new_password" name="new_password" required maxlength="8">
            <button type="submit" id="submit">Change Password</button>
        </form>
    </div>
</body>

</html>