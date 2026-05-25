<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;
    $therapist_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $diagnosis = isset($_POST['diagnosis']) ? $_POST['diagnosis'] : '';
    $therapist_notes = isset($_POST['therapist_notes']) ? $_POST['therapist_notes'] : '';

    if ($patient_id && $therapist_id) {
        // ON DUPLICATE KEY UPDATE inserează rândul dacă nu există, sau îi dă UPDATE dacă există deja
        $stmt = $db->prepare("
            INSERT INTO patient_medical_records (patient_id, therapist_id, diagnosis, therapist_notes) 
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE diagnosis = ?, therapist_notes = ?");
        $stmt->execute([$patient_id, $therapist_id, $diagnosis, $therapist_notes, $diagnosis, $therapist_notes]);

        $_SESSION['client_msg'] = "✅ Fișa pacientului a fost salvată cu succes!";
    }
}
header("Location: my_clients.php");
exit();