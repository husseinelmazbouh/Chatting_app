<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

$connection = new mysqli("localhost", "root", "", "chatting_app");

if ($connection->connect_error) {
    die("connection error: " . $connection->connect_error);
}
?>