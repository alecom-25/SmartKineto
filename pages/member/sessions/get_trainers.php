<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

// Verificăm ce tip a cerut JS-ul
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Stabilim ce rol căutăm în tabelul users
$rol_cautat = ($type === 'kineto') ? 'kineto' : 'trainer';

// Căutăm antrenorii și le aducem numele din user_details
$stmt = $db->prepare("SELECT u.id, ud.nume, ud.prenume FROM users u 
    JOIN user_details ud ON u.id = ud.user_id WHERE u.role = ?");
$stmt->execute([$rol_cautat]);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Returnăm datele în format JSON
echo json_encode($staff);