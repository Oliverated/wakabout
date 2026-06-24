<?php
// Load the Env class and parse the .env file (idempotent — safe to call multiple times)
// require_once __DIR__ . '/../env/env.php';
// Env::load(__DIR__ . '/../env/.env');

// $conn = new mysqli(
//     Env::get('DB_HOST', 'localhost'),
//     Env::get('DB_USER', 'root'),
//     Env::get('DB_PASS', ''),
//     Env::get('DB_NAME', 'wakaabout')
// );

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }


// $servername = "localhost";
// $username = "u406949829_wakaabout";
// $password = "Pelu&892b!";
// $dbname = "u406949829_wakaabout";

// // Correct parameter order: servername, username, password, dbname
// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wakaabout";

// Correct parameter order: servername, username, password, dbname
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



?>


