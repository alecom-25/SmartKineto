<?php
// config.php
$host = "localhost";
$db_name = "kim_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Spunem PDO să arunce erori dacă interogările SQL sunt greșite
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexiune reușită!"; // Poți de-comenta asta doar pentru test
} catch (PDOException $e) {
    die("Eroare la conectare: " . $e->getMessage());
}
?>