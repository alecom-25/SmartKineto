<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kineto') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$therapist_id = $_SESSION['user_id'];
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;

//Daca exista, il stergem din tabela pacientilor
if ($patient_id) {
    $stmt = $db->prepare("DELETE FROM patient_medical_records WHERE patient_id = ? AND therapist_id = ?");
    $stmt->execute([$patient_id, $therapist_id]);

    $_SESSION['client_msg'] = " Pacientul a fost eliminat cu succes din listă.";
}

header("Location: my_clients.php");
exit();

