<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$date = isset($_GET['date']) ? $_GET['date'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : '';

if (!$date || !$time) {
    echo json_encode([]);
    exit();
}

// formatam ora
$time = substr($time, 0, 5);

// Căutăm toate sălile și NUMĂRĂM câți oameni sunt deja aprobați în ele la ora și data cerută
$stmt = $db->prepare(" SELECT r.id, r.name, r.capacity, r.type, (SELECT COUNT(*) FROM appointments a 
            WHERE a.room_id = r.id AND a.booking_date = ? AND a.start_time = ? 
            AND a.status IN ('approved', 'rescheduled')) as occupied FROM rooms r");
$stmt->execute([$date, $time]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rooms);