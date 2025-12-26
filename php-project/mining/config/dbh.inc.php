<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$servername ="localhost";
$dBUsername ="root";
$dBPassword ="";
$dBName ="my_gamez";

$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

if (!$conn) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit;
}

?>