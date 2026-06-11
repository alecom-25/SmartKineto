<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

// 1. Utilizatori
$stmt = $db->query("\n    SELECT u.id, u.username, u.email, u.role, u.created_at,\n           ud.nume, ud.prenume, ud.telefon\n    FROM users u\n    LEFT JOIN user_details ud ON ud.user_id = u.id\n    ORDER BY u.id\n");
$users = array_map('public_user', $stmt->fetchAll(PDO::FETCH_ASSOC));

// 2. Săli
$stmt = $db->query("SELECT id, name, capacity, type FROM rooms ORDER BY id");
$roomsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rooms = array_map(function ($r) {
    return [
        'id' => (string)$r['id'],
        'name' => $r['name'],
        'capacity' => (int)$r['capacity'],
        'equipment' => ucfirst($r['type']),
        'type' => $r['type'],
    ];
}, $roomsRows);

// 3. Abonamente
$stmt = $db->query("SELECT * FROM subscriptions ORDER BY id");
$subscriptions = array_map(function ($s) {
    $features = [];
    if (!empty($s['has_fitness'])) $features[] = 'Fitness';
    if (!empty($s['has_forta'])) $features[] = 'Forță';
    if (!empty($s['has_kineto'])) $features[] = 'Kineto';
    if (!empty($s['has_vip_perks'])) $features[] = 'VIP';
    $type = strtoupper($s['tier'] ?? 'membru') . (count($features) ? ' - ' . implode(', ', $features) : '');

    return [
        'id' => (string)$s['id'],
        'userId' => (string)$s['user_id'],
        'type' => $type,
        'start' => $s['start_date'] ?: '',
        'end' => $s['expires_at'] ?: '',
        'price' => estimate_subscription_price($s),
        'status' => subscription_status($s),
    ];
}, $stmt->fetchAll(PDO::FETCH_ASSOC));

// 4. Programări deja existente, grupate ca sesiuni afișabile în frontend.
$stmt = $db->query("\n    SELECT a.staff_id, a.booking_date, a.start_time, a.end_time, a.session_type_id, a.room_id,\n           st.name AS session_name, st.category, st.location,\n           r.name AS room_name, r.capacity AS room_capacity,\n           GROUP_CONCAT(CASE WHEN a.status NOT IN ('cancelled','rejected') THEN a.user_id END) AS booked_users,\n           COUNT(CASE WHEN a.status NOT IN ('cancelled','rejected') THEN 1 END) AS booked_count\n    FROM appointments a\n    LEFT JOIN session_types st ON st.id = a.session_type_id\n    LEFT JOIN rooms r ON r.id = a.room_id\n    WHERE a.booking_date IS NOT NULL AND a.start_time IS NOT NULL\n    GROUP BY a.staff_id, a.booking_date, a.start_time, a.session_type_id, a.room_id\n    ORDER BY a.booking_date, a.start_time\n");
$sessions = [];
$usedSlots = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $time = substr((string)$row['start_time'], 0, 5);
    $timeKey = str_replace(':', '', $time);
    $staffId = (string)$row['staff_id'];
    $date = (string)$row['booking_date'];
    $typeId = (string)($row['session_type_id'] ?? 0);
    $roomId = (string)($row['room_id'] ?? 0);
    $key = $staffId . '_' . $date . '_' . $timeKey;
    $usedSlots[$key] = true;

    $booked = [];
    if (!empty($row['booked_users'])) {
        $booked = array_values(array_unique(array_filter(explode(',', $row['booked_users']))));
        $booked = array_map('strval', $booked);
    }

    $capacity = (int)($row['room_capacity'] ?? 1);
    if ($capacity <= 0) $capacity = 1;

    $sessions[] = [
        'id' => 'grp_' . $staffId . '_' . $date . '_' . $timeKey . '_' . $typeId . '_' . $roomId,
        'title' => $row['session_name'] ?: 'Sesiune SmartKineto',
        'type' => map_session_type_to_frontend($row['category'] ?? null),
        'trainer' => $staffId,
        'room' => $roomId !== '0' ? $roomId : '',
        'date' => $date,
        'time' => $time,
        'duration' => 60,
        'capacity' => $capacity,
        'booked' => $booked,
        'status' => 'active',
        'description' => trim(($row['category'] ?? '') . ' ' . ($row['location'] ?? '')),
    ];
}

// 5. Sloturi libere generate din staff_availability, ca membrul să poată rezerva din interfața nouă.
$stmt = $db->query("\n    SELECT sa.trainer_id, sa.available_date, sa.start_time, sa.end_time, u.role\n    FROM staff_availability sa\n    JOIN users u ON u.id = sa.trainer_id\n    WHERE sa.available_date >= CURDATE()\n    ORDER BY sa.available_date, sa.start_time\n");
$availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($availability as $av) {
    $staffId = (string)$av['trainer_id'];
    $date = (string)$av['available_date'];
    $start = strtotime($date . ' ' . $av['start_time']);
    $end = strtotime($date . ' ' . $av['end_time']);

    if (!$start || !$end || $end <= $start) continue;

    $requiredRole = ($av['role'] === 'kineto') ? 'kineto' : 'trainer';

    $stmtType = $db->prepare("SELECT id, name, category, location FROM session_types WHERE required_role = ? ORDER BY id LIMIT 1");
    $stmtType->execute([$requiredRole]);
    $typeRow = $stmtType->fetch(PDO::FETCH_ASSOC);
    if (!$typeRow) continue;

    $roomType = ($requiredRole === 'kineto') ? 'kineto' : 'fitness';
    $stmtRoom = $db->prepare("SELECT id, name, capacity FROM rooms WHERE type = ? ORDER BY id LIMIT 1");
    $stmtRoom->execute([$roomType]);
    $roomRow = $stmtRoom->fetch(PDO::FETCH_ASSOC);
    if (!$roomRow) continue;

    for ($t = $start; $t < $end; $t += 3600) {
        if ($t <= time()) continue;

        $time = date('H:i', $t);
        $timeKey = str_replace(':', '', $time);
        $key = $staffId . '_' . $date . '_' . $timeKey;
        if (isset($usedSlots[$key])) continue;

        $sessions[] = [
            'id' => 'slot_' . $staffId . '_' . $date . '_' . $timeKey . '_' . $typeRow['id'] . '_' . $roomRow['id'],
            'title' => $typeRow['name'] ?: 'Sesiune disponibilă',
            'type' => map_session_type_to_frontend($typeRow['category'] ?? null),
            'trainer' => $staffId,
            'room' => (string)$roomRow['id'],
            'date' => $date,
            'time' => $time,
            'duration' => 60,
            'capacity' => (int)$roomRow['capacity'],
            'booked' => [],
            'status' => 'active',
            'description' => 'Slot liber generat din programul specialistului.',
        ];
    }
}

usort($sessions, function ($a, $b) {
    return strcmp($a['date'] . $a['time'], $b['date'] . $b['time']);
});

// 6. Activitate / istoric
$stmt = $db->query("\n    SELECT ah.id, ah.created_at, ah.description, ah.activity_type, u.username, ud.nume, ud.prenume\n    FROM activities_history ah\n    LEFT JOIN users u ON u.id = ah.user_id\n    LEFT JOIN user_details ud ON ud.user_id = ah.user_id\n    ORDER BY ah.created_at DESC\n    LIMIT 30\n");
$activity = array_map(function ($a) {
    return [
        'id' => (string)$a['id'],
        'date' => substr((string)$a['created_at'], 0, 10),
        'action' => $a['activity_type'] ?: 'Activitate',
        'user' => full_name($a['nume'] ?? null, $a['prenume'] ?? null, $a['username'] ?? null),
        'detail' => $a['description'] ?? '',
    ];
}, $stmt->fetchAll(PDO::FETCH_ASSOC));

$equipment = array_map(function ($r) {
    return [
        'id' => 'eq_' . $r['id'],
        'name' => 'Echipamente ' . $r['name'],
        'qty' => 1,
        'room' => (string)$r['id'],
    ];
}, $roomsRows);

$plugins = [
    ['id' => 'p1', 'name' => 'Notificări Email', 'description' => 'Trimite notificări pentru rezervări.', 'status' => 'installed', 'version' => '1.0.0'],
    ['id' => 'p2', 'name' => 'Export Rapoarte', 'description' => 'Export CSV / XML pentru statistici.', 'status' => 'installed', 'version' => '1.0.0'],
];

json_response([
    'success' => true,
    'users' => $users,
    'sessions' => $sessions,
    'subscriptions' => $subscriptions,
    'rooms' => $rooms,
    'equipment' => $equipment,
    'plugins' => $plugins,
    'activity' => $activity,
]);
