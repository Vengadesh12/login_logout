<?php
$host = "localhost";
$user = "root";  // Change this based on your DB setup
$pass = "";
$dbname = "user_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
