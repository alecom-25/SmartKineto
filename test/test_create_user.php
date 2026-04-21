<?php
// create_test_user.php
require_once '../src/Database.php';

$db = Database::getInstance()->getConnection();
$user = "alexia_25";
$email = "alecom@gmail.com";
$pass = "alexia";

$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'member')";
$stmt = $db->prepare($sql);
$stmt->execute([$user, $email, $hashed_pass]);

echo "Utilizator creat cu succes! Acum poti testa login-ul cu parola: " . $pass;
