<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $therapist_id = $_SESSION['user_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $session_type_id = $_POST['session_type_id'];
    $room_id = $_POST['room_id'];

    // Inserăm ședința ca fiind deja aprobată
    $stmt = $db->prepare("INSERT INTO appointments (user_id, staff_id, session_type_id, booking_date, start_time, room_id, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->execute([$patient_id, $therapist_id, $session_type_id, $booking_date, $start_time, $room_id]);

    // Opțional: Inserăm și în istoricul de activități (activity log)
    $log_desc = "Kinetoterapeutul ți-a programat o nouă procedură pe data de $booking_date la ora $start_time.";
    $db->prepare("INSERT INTO activities_history (user_id, activity_type, description, created_at) VALUES (?, 'session', ?, NOW())")->execute([$patient_id, $log_desc]);

    $_SESSION['client_msg'] = "Programarea pacientului a fost salvată!";
}

header("Location: my_clients.php");
exit();