<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}


header('Content-Type: text/xml; charset=utf-8');
header('Content-Disposition: attachment; filename=raport_statistici_' . date('Y-m-d') . '.xml');

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><raport_statistici></raport_statistici>');

// statistici utilizatori
$utilizatoriNode = $xml->addChild('utilizatori_activi');
$stmtUsers = $db->query("SELECT role, COUNT(id) as total FROM users GROUP BY role");
while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    $item = $utilizatoriNode->addChild('grup');
    $item->addChild('rol', $row['role']);
    $item->addChild('total', $row['total']);
}

// statistici staff
$staffNode = $xml->addChild('top_staff');
$stmtTopStaff = $db->query("
    SELECT ud.nume, ud.prenume, COUNT(a.id) as total_sessions FROM appointments a 
    JOIN user_details ud ON a.staff_id = ud.user_id GROUP BY a.staff_id ORDER BY total_sessions DESC LIMIT 5");
while ($row = $stmtTopStaff->fetch(PDO::FETCH_ASSOC)) {
    $angajat = $staffNode->addChild('angajat');
    $angajat->addChild('nume', htmlspecialchars($row['nume'] . ' ' . $row['prenume']));
    $angajat->addChild('sesiuni_rezervate', $row['total_sessions']);
}

// statistici abonamente
$abonamenteNode = $xml->addChild('distributie_abonamente');
$toate_abonamentele = ['fitness' => 0, 'forta' => 0, 'kineto' => 0, 'tip1' => 0, 'tip2' => 0, 'tip3' => 0, 'vip' => 0];
$stmtSubs = $db->query("SELECT tier, has_fitness, has_forta, has_kineto FROM subscriptions WHERE is_suspended = 0");
$subsData = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

foreach($subsData as $row) {
    if($row['tier'] == 'membru') {
        if($row['has_fitness'] == 1) { $toate_abonamentele['fitness'] += 1; }
        elseif($row['has_forta'] == 1) { $toate_abonamentele['forta'] += 1; }
        elseif($row['has_kineto'] == 1) { $toate_abonamentele['kineto'] += 1; }
    } elseif($row['tier'] == 'premium') {
        if($row['has_fitness'] == 1 && $row['has_forta'] == 1) { $toate_abonamentele['tip1'] += 1; }
        elseif($row['has_fitness'] == 1 && $row['has_kineto'] == 1) { $toate_abonamentele['tip2'] += 1; }
        elseif($row['has_fitness'] == 0) { $toate_abonamentele['tip3'] += 1; }
    } else {
        $toate_abonamentele['vip'] += 1;
    }
}

foreach($toate_abonamentele as $tip => $total) {
    $abItem = $abonamenteNode->addChild('abonament');
    $abItem->addChild('tip', $tip);
    $abItem->addChild('numar_clienti', $total);
}

// statistici sesiuni
$sesiuniNode = $xml->addChild('volum_programari');
$sessionsDay = $db->query("SELECT COUNT(*) FROM appointments WHERE booking_date = CURDATE() AND status != 'cancelled'")->fetchColumn();
$sessionsWeek = $db->query("SELECT COUNT(*) FROM appointments WHERE YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE(), 1) AND status != 'cancelled'")->fetchColumn();
$sessionsMonth = $db->query("SELECT COUNT(*) FROM appointments WHERE MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND status != 'cancelled'")->fetchColumn();

$sesiuniNode->addChild('azi', $sessionsDay);
$sesiuniNode->addChild('saptamana_curenta', $sessionsWeek);
$sesiuniNode->addChild('luna_curenta', $sessionsMonth);

echo $xml->asXML();
exit();