<?php
require_once __DIR__ . '/../../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

// extragem datele din db
$stmt = $db->query("SELECT u.id, ud.nume, ud.prenume, u.email, u.role FROM users u 
    JOIN user_details ud ON u.id = ud.user_id WHERE u.role IN ('trainer', 'kineto') ORDER BY u.id ASC");
$staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// setam header-ele pt fortarea descarcarii in xml
header('Content-Type: text/xml; charset=utf-8');
header('Content-Disposition: attachment; filename=staff_smartkineto_' . date('Y-m-d') . '.xml');

// initializam un obiectSimpleXMLElement, cu tag
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><staff_smartkineto></staff_smartkineto>');

// construim structura arborescenta in fct de date
foreach ($staff_list as $row) {
    // cream un nod pt fiecare angajat
    $angajat = $xml->addChild('angajat');
    $rol_afisat = ($row['role'] === 'kineto') ? 'Kinetoterapeut' : 'Antrenor';

    // adaugam detalii
    $angajat->addChild('id', $row['id']);
    $angajat->addChild('nume', htmlspecialchars($row['nume']));
    $angajat->addChild('prenume', htmlspecialchars($row['prenume']));
    $angajat->addChild('email', htmlspecialchars($row['email']));
    $angajat->addChild('rol', $rol_afisat);
}

//trimitem codu generat la browser pt descarcare
echo $xml->asXML();
exit();