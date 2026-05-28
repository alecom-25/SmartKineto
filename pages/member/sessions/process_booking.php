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

//VERIFICAM DACA ARE ABONAMENTUL CORESPUNZATOR PENTRU A REZERVA SESIUNEA DORITA
if($type === 'kineto' && $category === 'masaj') {
    if ($active_sub['has_kineto'] == 0) {
        $_SESSION['msj_red'] = "Preț de achitat la recepție: 175 RON.";
    }else{
        $_SESSION['msj_red'] = "Beneficiezi de 35% reducere (Preț de achitat la locație: 113.75 RON)";
    }
}

if ($type === 'kineto' && $active_sub['has_fitness'] == 0 && $category === 'evaluare') {
    $_SESSION['error_msg'] = "Abonamentul tău curent nu include sesiuni de evaluare la Kinetoterapie";
    header("Location: appointments.php");
    exit();
}

if ($type === 'fitness' && $active_sub['has_fitness'] == 0) {
    $_SESSION['error_msg'] = "Abonamentul tău curent nu include sesiuni de Fitness";
    header("Location: appointments.php");
    exit();
}

//VERIFICAM DACA SESIUNEA DORITA NU SE SUPRAPUNE CU ALTE SESIUNI DEJA CONFIRMATE
$stmtOverlap = $db->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND booking_date = ? 
      AND start_time = ? AND status != 'cancelled'");
$stmtOverlap->execute([$user_id, $date, $time]);
$is_overlapping = $stmtOverlap->fetchColumn();

if ($is_overlapping > 0) {
    // daca se afla si in alta sesiune la aceeasi data nu ii permitem inregistrarea alteia in acelasi timp
    $_SESSION['error_msg'] = "Nu te poți programa! Ai deja o altă sesiune rezervată la exact aceeași oră.";
    header("Location: appointments.php");
    exit();
}

//ALTFEL, INSEGISTRAM SESIUNEA
// 1. Mapăm datele din formular la categoriile din baza noastră de date
$db_category = '';
$db_location = '';

if ($type == 'kineto') {
    $db_category = ($category == 'masaj') ? 'kineto_masaj' : 'kineto_examen';
} else if ($type == 'fitness') {
    $db_category = ($category == 'grup') ? 'fitness_group' : 'personal_training';
}

if ($location == 'exterior') {
    $db_location = 'exterior';
} else {
    // Dacă e interior, Personal Training-ul e de obicei în "sala_aparate", grupul în "sala_fitness"
    //$db_location = ($db_category == 'personal_training') ? 'sala_aparate' : 'sala_fitness';
    if($db_category == 'personal_training'){
        $db_location = 'sala_aparate';
    } else {
        $db_location = 'sala_fitness';
    }
    if ($type == 'kineto'){
        $db_location = 'sala_kineto';
    }
}

echo $db_category; echo $db_location;

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
