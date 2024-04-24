<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Unset admin session variable
// unset($_SESSION['admin']);
unset($_SESSION['id']);

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit();
