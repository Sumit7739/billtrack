<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, 
    initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <title>OTP Verification</title>
    <style>
        body {
            /* font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px; */
            background: #abcdef;
            font-family: Assistant, sans-serif;
            display: flex;
            min-height: 50vh;
            justify-content: center;
            align-items: center;
        }

        h1 {
            position: absolute;
            top: 1%;
            color: #333;
            text-align: center;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        h2 {
            position: relative;
            /* top: 12%; */
            color: #fff;
            text-align: center;
            font-size: 1.3em;
            max-width: 400px;
            margin-bottom: 100px;
        }

        form {
            position: absolute;
            top: 30%;
            color: white;
            background: #7851A9;
            background: -webkit-linear-gradient(to right, #267871, #7851A9);
            background: #fff;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.2), 0px 10px 20px rgba(0, 0, 0, 0.3), 0px 30px 60px 1px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            border: 1px solid #000;
            width: 80%;
            height: 20%;
            max-width: 200px;
            padding: 80px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            border: 1px solid #fff;
            border-radius: 8px;
            box-shadow: rgba(0, 0, 0, 0.69) 0px 5px 15px;
        }

        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 1em;
            color: #fff;
            background: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
        }

        input[type="submit"]:hover {
            background: #555;
        }

        form a {
            /* position: absolute; */
            text-decoration: none;
            /* margin-top: 30px; */
            margin-left: 55px;
            /* padding: 10px 10px; */
        }

        form h4 {
            position: absolute;
            color: #000;
            left: 0%;
            margin-top: 80px;
            margin-left: 20px;
        }
    </style>
</head>

<body>
    <?php
    include('config.php');

    // Establish a database connection using the constants from config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    // Check if email is provided as a URL parameter
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
    ?>
        <h1>Verify Your Account to reset password</h1>
        <h2>An OTP is sent to your entered Email Address</h2>
        <form action="process_otp_2.php" method="POST">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <input type="text" id="otp" name="otp" placeholder="Enter OTP" required maxlength="6">
            <br><br>
            <input type="submit" value="Verify OTP">
            <h4>Did not recieved OTP?
                <a href="send_otp.php">Resend Otp</a>
            </h4>
        </form>
    <?php
    } else {
        echo 'Invalid request.';
    }

    $conn->close();
    ?>
</body>

</html>