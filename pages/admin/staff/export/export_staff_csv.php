<?php
require_once __DIR__ . '/../../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

// extragem datele antrenorilor din db
$stmt = $db->query("SELECT u.id, ud.nume, ud.prenume, u.email, u.role FROM users u 
    JOIN user_details ud ON u.id = ud.user_id WHERE u.role IN ('trainer', 'kineto') ORDER BY u.id ASC");
$staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// setam header-ele html pt a forta descarcarea in csv
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=staff_smartkineto_' . date('Y-m-d') . '.csv');

// deschidem un stream pt a scrie direct in fisier
$output = fopen('php://output', 'w');

// adaugam BOM (Byte Order Mark) pt ca excel sa recunoasca diacriticile
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// scriem denumirile coloanelor
fputcsv($output, ['ID Angajat', 'Nume', 'Prenume', 'Email Contact', 'Rol / Specializare']);

// parcurgem datele si le adaugam in fisier
foreach ($staff_list as $row) {
    $rol_afisat = ($row['role'] === 'kineto') ? 'Kinetoterapeut' : 'Antrenor';

    fputcsv($output, [$row['id'], $row['nume'], $row['prenume'], $row['email'], $rol_afisat]);
}

//la final, inchidem fisieru si iesim
fclose($output);
exit();