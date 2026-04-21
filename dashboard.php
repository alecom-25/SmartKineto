<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$numeUtilizator = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SmartKineto</title>
</head>
<body>
    <h1>Salut, <?php echo $numeUtilizator; ?>!</h1>
    <p>Te-ai logat cu succes în aplicația SmartKineto.</p>

    <br>
    <a href="logout.php">Ieși din cont (Logout)</a>
</body>
</html>
