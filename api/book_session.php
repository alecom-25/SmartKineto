<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

$data = read_json_body();
$sessionId = (string)($data['session_id'] ?? '');

if ($sessionId === '') {
    json_response(['success' => false, 'message' => 'Sesiunea nu a fost transmisă.'], 400);
}

$parts = explode('_', $sessionId);
if (count($parts) < 6 || !in_array($parts[0], ['slot', 'grp'], true)) {
    json_response(['success' => false, 'message' => 'ID sesiune invalid.'], 400);
}

[$kind, $staffId, $date, $timeKey, $sessionTypeId, $roomId] = $parts;
$time = substr($timeKey, 0, 2) . ':' . substr($timeKey, 2, 2) . ':00';
$userId = (int)$_SESSION['user_id'];

// Verificăm abonament activ.
$stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY expires_at DESC LIMIT 1");
$stmt->execute([$userId]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sub || subscription_status($sub) !== 'active') {
    json_response(['success' => false, 'message' => 'Ai nevoie de un abonament activ pentru rezervare.'], 403);
}

// Evităm dublarea rezervării aceluiași utilizator pe același slot.
$stmt = $db->prepare("\n    SELECT id FROM appointments\n    WHERE user_id = ? AND staff_id = ? AND booking_date = ? AND start_time = ?\n      AND session_type_id = ? AND (room_id = ? OR room_id IS NULL)\n      AND status NOT IN ('cancelled','rejected')\n    LIMIT 1\n");
$stmt->execute([$userId, $staffId, $date, $time, $sessionTypeId, $roomId]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => 'Ești deja rezervat la această sesiune.'], 409);
}

$stmt = $db->prepare("\n    INSERT INTO appointments (user_id, session_type_id, room_id, staff_id, booking_date, start_time, status, notes)\n    VALUES (?, ?, ?, ?, ?, ?, 'approved', 'Rezervare din frontend API')\n");
$stmt->execute([$userId, $sessionTypeId, $roomId ?: null, $staffId, $date, $time]);

$stmtType = $db->prepare("SELECT name FROM session_types WHERE id = ?");
$stmtType->execute([$sessionTypeId]);
$title = $stmtType->fetchColumn() ?: 'Sesiune';

$stmtHist = $db->prepare("INSERT INTO activities_history (user_id, activity_type, description, amount) VALUES (?, 'session', ?, 0)");
$stmtHist->execute([$userId, $title . ' - rezervare din frontend']);

json_response(['success' => true, 'message' => 'Rezervare salvată în baza de date.']);
