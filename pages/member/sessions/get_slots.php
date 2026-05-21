<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$trainer_id = $_GET['trainer_id'];
$date = $_GET['date'];

// 1. Luăm intervalele teoretice ale antrenorului din staff_availability
$stmt = $db->prepare("SELECT start_time, end_time FROM staff_availability WHERE trainer_id = ? AND available_date = ?");
$stmt->execute([$trainer_id, $date]);
$availability = $stmt->fetch();

if (!$availability) {
    echo json_encode(["lipsa_program"]);
    exit();
}

// 2. Luăm programările deja existente pentru acea zi ca să le eliminăm
$stmtBooked = $db->prepare("SELECT start_time FROM appointments WHERE staff_id = ? AND booking_date = ? AND status != 'rejected' AND status != 'pending'");
$stmtBooked->execute([$trainer_id, $date]);
$booked_slots = $stmtBooked->fetchAll(PDO::FETCH_COLUMN);

// 3. Generăm intervalele de 1 oră
$available_slots = [];
$start = strtotime($availability['start_time']);
$end = strtotime($availability['end_time']);

for ($i = $start; $i < $end; $i += 3600) {
    $time = date('H:i', $i);
    if (!in_array($time . ":00", $booked_slots)) {
        $available_slots[] = $time;
    }
}

echo json_encode($available_slots);