<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$user_id = $_SESSION['user_id'];

//Verificam daca are abonament activ pt a accesa pagina
$stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expires_at >= CURDATE() AND is_suspended = 0 LIMIT 1");
$stmt->execute([$user_id]);
$active_sub = $stmt->fetch();

if (!$active_sub) {
    // Dacă nu are abonament, îl trimitem la pagina de planuri cu un mesaj
    header("Location: choose_plan.php?error=no_active_sub");
    exit();
}

// Verificăm dacă a venit din formular (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: appointments.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'];             // 'fitness' sau 'kineto'
$category = $_POST['category'];     // 'individual' sau 'grup'
$location = $_POST['location'];     // 'interior' sau 'exterior'
$trainer_id = $_POST['trainer_id'];
$date = $_POST['date'];
$time = $_POST['time'];

// 1. Mapăm datele din formular la categoriile din baza noastră de date (session_types)
$db_category = '';
$db_location = '';

if ($type == 'kineto') {
    $db_category = 'kineto';
} else if ($type == 'fitness') {
    $db_category = ($category == 'grup') ? 'fitness_group' : 'personal_training';
}

if ($location == 'exterior') {
    $db_location = 'exterior';
} else {
    // Dacă e interior, Personal Training-ul e de obicei în "sala_aparate", grupul în "sala_fitness"
    $db_location = ($db_category == 'personal_training') ? 'sala_aparate' : 'sala_fitness';
}

// 2. Găsim ID-ul corespunzător în session_types
$stmtType = $db->prepare("SELECT id FROM session_types WHERE category = ? AND location = ? LIMIT 1");
$stmtType->execute([$db_category, $db_location]);
$sessionType = $stmtType->fetch(PDO::FETCH_ASSOC);

if (!$sessionType) {
    die("Eroare: Tipul de ședință selectat nu există în sistem.");
}

$session_type_id = $sessionType['id'];

// 3. Inserăm rezervarea în tabelul appointments
try {
    $stmtInsert = $db->prepare("
        INSERT INTO appointments (user_id, staff_id, session_type_id, booking_date, start_time, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')");

    $stmtInsert->execute([$user_id, $trainer_id, $session_type_id, $date, $time]);

    // 4. Setăm notificarea pentru flash message
    $_SESSION['dismissed_bookings'] = []; // Resetăm lista de notificări închise ca să o vadă pe cea nouă
    $_SESSION['flash_message'] = "Cererea ta de rezervare pentru $date, ora $time a fost trimisă și este în așteptare.";

    // 5. Redirecționăm înapoi la panoul de ședințe
    header("Location: appointments.php");
    exit();

} catch (PDOException $e) {
    // Dacă apare o eroare la baza de date (ex: constrângeri)
    die("A apărut o eroare la salvarea programării: " . $e->getMessage());
}
