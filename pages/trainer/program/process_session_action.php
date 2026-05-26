<?php
require_once __DIR__ . '/../../../init.php';

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

// fct de email
function sendNotificationEmail($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: SmartKineto <no-reply@smartkineto.ro>" . "\r\n";

    // În realitate se trimite. Pentru XAMPP va rula, dar e posibil să nu plece pe rețea fără setări.
    mail($to, $subject, $message, $headers);
}

// 3. Procesăm Acțiunea
try {
    $room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;

    if ($action === 'approve') {
        $stmtroom = $db->prepare("SELECT name FROM rooms WHERE id = ?");
        $stmtroom->execute([$room_id]);
        $roomName = $stmtroom->fetchColumn();

        $db->prepare("UPDATE appointments SET status = 'approved', room_id = ? WHERE id = ?")->execute([$room_id, $id]);
        $desc = "$session_name in $roomName";
        $db->prepare("INSERT INTO  activities_history (user_id, activity_type, description) VALUES (?, 'session', ?)")->execute([$user_id, $desc]);

        $msg = "Salut! Ședința ta de <strong>$session_name</strong> din data de $old_date ($old_time) a fost aprobată de antrenor. Te așteptăm în: <strong>$roomName</strong>";
        sendNotificationEmail($user_email, "Ședință Aprobată - SmartKineto", $msg);

        $_SESSION['flash_msg'] = "Ședința a fost aprobată si sala a fost rezervata!";

    } elseif ($action === 'cancel') {
        $db->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?")->execute([$id]);

        $msg = "Salut! Din păcate, ședința de <strong>$session_name</strong> din data de $old_date a fost anulată de antrenor.";
        sendNotificationEmail($user_email, "Ședință Anulată - SmartKineto", $msg);

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

        $msg = "Salut! Ședința ta de <strong>$session_name</strong> a fost reprogramată. <br>
                Data veche: $old_date ($old_time) <br> <strong>Data nouă: $new_date ($new_time)</strong> 
                Locația nouă: <strong>$roomName</strong>.";
        sendNotificationEmail($user_email, "Ședință Reprogramată - SmartKineto", $msg);

        $_SESSION['flash_msg'] = "Ședința a fost reprogramată cu succes! Data nouă: $new_date ($new_time)";
    }

    // 4. La final, ne întoarcem la pagina de gestiune
    header("Location: manage_sessions.php");
    exit();

} catch (Exception $e) {
    die("Eroare SQL: " . $e->getMessage());
}