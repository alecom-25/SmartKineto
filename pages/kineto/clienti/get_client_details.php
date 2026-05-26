<?php
require_once __DIR__ . '/../../../init.php';

$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;
$therapist_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$patient_id || !$therapist_id) {
    echo json_encode(['error' => 'Date invalide']);
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}


// 1. Luăm diagnosticul și notele din tabela medicală
$stmtRecord = $db->prepare("SELECT diagnosis, therapist_notes FROM patient_medical_records WHERE patient_id = ? AND therapist_id = ?");
$stmtRecord->execute([$patient_id, $therapist_id]);
$record = $stmtRecord->fetch(PDO::FETCH_ASSOC) ?: ['diagnosis' => '', 'therapist_notes' => ''];

// 2. Luăm vârsta (calculată din data nașterii)
$stmtAge = $db->prepare("SELECT data_nasterii FROM user_details WHERE user_id = ?");
$stmtAge->execute([$patient_id]);
$birth_date = $stmtRoom = $stmtAge->fetchColumn();
$age = 'Nespecificată';
if ($birth_date) {
    $age = date_diff(date_create($birth_date), date_create('now'))->y . ' ani';
}

// 3. Luăm ședințele TRECUTE (Istoric)
$stmtPast = $db->prepare("
    SELECT a.booking_date, a.start_time, st.name as session_name, r.name as room_name
    FROM appointments a
    JOIN session_types st ON a.session_type_id = st.id
    LEFT JOIN rooms r ON a.room_id = r.id
    WHERE a.user_id = ? AND a.staff_id = ? AND (a.booking_date < CURDATE() OR (a.booking_date = CURDATE() AND a.start_time <= CURTIME()))
    ORDER BY a.booking_date DESC, a.start_time DESC LIMIT 5");
$stmtPast->execute([$patient_id, $therapist_id]);
$past_sessions = $stmtPast->fetchAll(PDO::FETCH_ASSOC);

// 4. Luăm ședințele viitoare
$stmtFuture = $db->prepare("
    SELECT a.booking_date, a.start_time, st.name as session_name, r.name as room_name, a.status
    FROM appointments a
    JOIN session_types st ON a.session_type_id = st.id
    LEFT JOIN rooms r ON a.room_id = r.id
    WHERE a.user_id = ? AND a.staff_id = ? AND (a.booking_date > CURDATE() OR (a.booking_date = CURDATE() AND a.start_time > CURTIME()))
    ORDER BY a.booking_date ASC, a.start_time ASC");
$stmtFuture->execute([$patient_id, $therapist_id]);
$future_sessions = $stmtFuture->fetchAll(PDO::FETCH_ASSOC);

// Trimitem pachetul complet de date către JavaScript
echo json_encode([
    'age' => $age,
    'record' => $record,
    'past' => $past_sessions,
    'future' => $future_sessions
]);