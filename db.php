<?php
$host = "localhost";
$user = "root";
$password = ""; // Default XAMPP password is empty
$database = "abcd_db";

$db = new mysqli($host, $user, $password, $database);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
