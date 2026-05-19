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
    header("Location: ../abonament/abonament.php");
    exit();
}

// --- LOGICA DISMISS NOTIFICARE ---
if (isset($_GET['dismiss']) && isset($_GET['status'])) {
    $notif_id = $_GET['dismiss'];
    $notif_status = $_GET['status'];
    // Salvăm id_status (ex: 5_approved sau 5_rescheduled)
    $_SESSION['dismissed_bookings'][] = $notif_id . '_' . $notif_status;
    header("Location: appointments.php");
    exit();
}

// --- 1. PRELUARE NOTIFICĂRI (Statusuri schimbate recent) ---
// Căutăm ședințe care nu sunt 'pending' (deci au fost procesate)
$stmtNotif = $db->prepare("SELECT a.id, a.status, st.name as session_name, a.booking_date 
                            FROM appointments a JOIN session_types st ON a.session_type_id = st.id
                            WHERE a.user_id = ? AND a.status IN ('approved', 'rejected', 'cancelled', 'rescheduled')");
$stmtNotif->execute([$user_id]);
$all_notifications = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);

// --- 2. PRELUARE ȘEDINȚE VIITOARE (Următoarele 7 zile) ---
$stmtUpcoming = $db->prepare("
    SELECT a.*, st.name as session_name, st.location, ud.nume as trainer_fname, ud.prenume as trainer_lname
    FROM appointments a JOIN session_types st ON a.session_type_id = st.id 
    JOIN user_details ud ON a.staff_id = ud.user_id WHERE a.user_id = ? 
    AND ( a.booking_date > CURDATE() OR (a.booking_date = CURDATE() AND a.start_time > CURTIME()))
    AND a.booking_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
    AND a.status IN ('approved', 'rescheduled')
    ORDER BY a.booking_date, a.start_time");
$stmtUpcoming->execute([$user_id]);
$upcoming_sessions = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

// --- 3. PRELUARE ISTORIC (Ultimele 5 ședințe trecute sau respinse) ---
$stmtHistory = $db->prepare("
    SELECT a.*, st.name as session_name, ud.nume as trainer_fname, ud.prenume as trainer_lname
    FROM appointments a JOIN session_types st ON a.session_type_id = st.id 
    JOIN user_details ud ON a.staff_id = ud.user_id
    WHERE a.user_id = ? AND ( a.booking_date < CURDATE() OR (a.booking_date = CURDATE() AND a.start_time <= CURTIME())
    OR a.status IN ('rejected', 'cancelled')) ORDER BY a.booking_date DESC LIMIT 10");
$stmtHistory->execute([$user_id]);
$history_sessions = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Ședințele Mele - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        /* Notificări */
        .alert {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            position: relative;
            display: flex;
            justify-content: space-between;
        }

        .alert-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-cancelled {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-rescheduled {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .close-notif {
            text-decoration: none;
            color: inherit;
            font-weight: bold;
            font-size: 20px;
        }

        /* Header */
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .btn-back:hover, .btn-new:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-new {
            background: #3498db;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        /* Secțiuni */
        h2 {
            border-left: 5px solid #3498db;
            padding-left: 15px;
            margin-top: 40px;
        }

        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .session-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #3498db;
        }

        .session-card.exterior {
            border-top-color: #2ecc71;
        }

        .date-badge {
            background: #f1f3f5;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f1f3f5;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-approved" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span>✅ <?php echo $_SESSION['flash_message']; ?></span>
            <a href="#" onclick="this.parentElement.style.display='none'; return false;" class="close-notif">&times;</a>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    <?php foreach ($all_notifications as $n):
        $notif_key = $n['id'] . '_' . $n['status'];
        if (isset($_SESSION['dismissed_bookings']) && in_array($notif_key, $_SESSION['dismissed_bookings'])) continue;
        ?>
        <div class="alert alert-<?php echo $n['status']; ?>">
            <span>
                🔔 Ședința de <strong><?php echo $n['session_name']; ?></strong> din data de <?php echo $n['booking_date']; ?>
                a fost <strong><?php echo strtoupper($n['status']); ?></strong>.
            </span>
            <a href="?dismiss=<?php echo $n['id']; ?>&status=<?php echo $n['status']; ?>" class="close-notif">&times;</a>
        </div>
    <?php endforeach; ?>

    <div class="header-nav">
        <a href="../../../dashboard.php" class="btn btn-back">← Dashboard</a>
        <a href="session_reserving.php" class="btn btn-new">➕ Rezervă Ședință Nouă</a>
    </div>

    <h1>Gestionare Ședințe</h1>

    <h2>📅 Program Săptămâna Viitoare</h2>
    <?php if (empty($upcoming_sessions)): ?>
        <p>Nu ai nicio ședință programată pentru următoarele 7 zile.</p>
    <?php else: ?>
        <div class="sessions-grid">
            <?php foreach ($upcoming_sessions as $s): ?>
                <div class="session-card <?php echo $s['location'] == 'exterior' ? 'exterior' : ''; ?>">
                    <div class="date-badge">
                        <?php echo date('d M Y', strtotime($s['booking_date'])); ?>
                        | <?php echo substr($s['start_time'], 0, 5); ?>
                    </div>
                    <h3><?php echo $s['session_name']; ?></h3>
                    <p>📍 Locație: <strong><?php echo ucfirst($s['location']); ?></strong></p>
                    <p>👤 Antrenor: <?php echo $s['trainer_fname'] . ' ' . $s['trainer_lname']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2>📜 Istoric Ultimele Ședințe</h2>
    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Tip Ședință</th>
            <th>Antrenor</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($history_sessions as $h): ?>
            <tr>
                <td><?php echo $h['booking_date']; ?></td>
                <td><?php echo $h['session_name']; ?></td>
                <td><?php echo $h['trainer_fname']; ?></td>
                <td>
                    <span style="color: <?php echo ($h['status'] == 'approved' or $h['status'] == 'rescheduled') ? 'green' : 'red'; ?>">
                        <?php echo strtoupper($h['status']); ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
