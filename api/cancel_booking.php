<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

$data = read_json_body();
$sessionId = (string)($data['session_id'] ?? '');
$parts = explode('_', $sessionId);

if (count($parts) < 6 || !in_array($parts[0], ['slot', 'grp'], true)) {
    json_response(['success' => false, 'message' => 'ID sesiune invalid.'], 400);
}

[$kind, $staffId, $date, $timeKey, $sessionTypeId, $roomId] = $parts;
$time = substr($timeKey, 0, 2) . ':' . substr($timeKey, 2, 2) . ':00';
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("\n    UPDATE appointments\n    SET status = 'cancelled'\n    WHERE user_id = ? AND staff_id = ? AND booking_date = ? AND start_time = ?\n      AND session_type_id = ? AND (room_id = ? OR room_id IS NULL)\n      AND status NOT IN ('cancelled','rejected')\n");
$stmt->execute([$userId, $staffId, $date, $time, $sessionTypeId, $roomId]);

if ($stmt->rowCount() === 0) {
    json_response(['success' => false, 'message' => 'Nu am găsit rezervarea de anulat.'], 404);
}

$stmtHist = $db->prepare("INSERT INTO activities_history (user_id, activity_type, description, amount) VALUES (?, 'session', 'Rezervare anulată din frontend', 0)");
$stmtHist->execute([$userId]);

json_response(['success' => true, 'message' => 'Rezervarea a fost anulată în baza de date.']);
