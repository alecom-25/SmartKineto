<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../mailer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$trainer_id = $_SESSION['user_id'];

// Preluăm datele (pot veni prin GET de la butoane sau POST de la formularul de reprogramare)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    $id = null;
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = null;
}

$custom_message = isset($_POST['custom_message']) ? trim($_POST['custom_message']) : '';

if (!$id || !$action) {
    die("Eroare: Date invalide.");
}

// 1. Aducem detaliile ședinței pentru a avea adresa de email a membrului și detaliile ședinței
$stmt = $db->prepare(" SELECT a.*, u.email as user_email, st.name as session_name FROM appointments a
    JOIN users u ON a.user_id = u.id JOIN session_types st ON a.session_type_id = st.id WHERE a.id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die("Eroare: Ședința nu există.");
}

$user_id = $appointment['user_id'];
$user_email = $appointment['user_email'];
$session_name = $appointment['session_name'];
$old_date = $appointment['booking_date'];
$old_time = substr($appointment['start_time'], 0, 5);

$info = $db->prepare("SELECT * FROM users WHERE id = ?");
$info->execute([$user_id]);
$user = $info->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM subscriptions WHERE id = ?");
$stmt->execute([$user_id]);
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

$sub = $subscription['has_kineto'];

// 3. Procesăm Acțiunea
try {
    $room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;

    if ($action === 'approve') {
        $stmtroom = $db->prepare("SELECT name FROM rooms WHERE id = ?");
        $stmtroom->execute([$room_id]);
        $roomName = $stmtroom->fetchColumn();
        $suma = 0;

        if($session_name == 'Masaj de relaxare'){
            if($sub == 1){
                $suma = 131.70;
            } else {
                $suma = 175;
            }
        }

        $db->prepare("UPDATE appointments SET status = 'approved', room_id = ? WHERE id = ?")->execute([$room_id, $id]);
        $desc = "$session_name in $roomName";
        $db->prepare("INSERT INTO  activities_history (user_id, activity_type, description, amount) VALUES (?, 'session', ?, ?)")->execute([$user_id, $desc, $suma]);

        trimiteMailConfirmare($user_email, $user['username'], $old_date, $old_time, $session_name, $roomName, $custom_message);

        $_SESSION['flash_msg'] = "Ședința a fost aprobată si sala a fost rezervata!";

    } elseif ($action === 'cancel') {
        $db->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?")->execute([$id]);

        trimiteMailAnulare($user_email, $user['username'], $old_date, $session_name, $custom_message);

        $_SESSION['flash_msg'] = "Ședința a fost anulată.";

    } elseif ($action === 'reschedule') {
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];
        $stmtroom = $db->prepare("SELECT name FROM rooms WHERE id = ?");
        $stmtroom->execute([$room_id]);
        $roomName = $stmtroom->fetchColumn();

        // Actualizăm cu noua dată și schimbăm statusul
        $db->prepare("UPDATE appointments SET status = 'rescheduled', booking_date = ?, start_time = ?, room_id = ? WHERE id = ?")
            ->execute([$new_date, $new_time,$room_id, $id]);

        trimiteMailReprogramare($user_email, $user['username'], $old_date, $old_time, $new_date, $new_time ,$session_name, $roomName, $custom_message);

        $_SESSION['flash_msg'] = "Ședința a fost reprogramată cu succes! Data nouă: $new_date ($new_time)";
    }

    // 4. La final, ne întoarcem la pagina de gestiune
    header("Location: manage_sessions.php");
    exit();

} catch (Exception $e) {
    die("Eroare SQL: " . $e->getMessage());
}