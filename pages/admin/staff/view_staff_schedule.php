<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$staff_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$staff_id) {
    die("Eroare: Nu ai selectat niciun membru al staff-ului.");
}

$stmtName = $db->prepare("SELECT nume, prenume FROM user_details WHERE user_id = ?");
$stmtName->execute([$staff_id]);
$staff = $stmtName->fetch();

if (!$staff) {
    die("Eroare: Membrul staff-ului nu există.");
}
$staff_name = $staff['nume'] . ' ' . $staff['prenume'];

// luam programul staff-ului
$stmtAvail = $db->prepare("SELECT available_date, start_time, end_time FROM staff_availability WHERE trainer_id = ? AND available_date >= CURDATE() ORDER BY available_date ASC");
$stmtAvail->execute([$staff_id]);
$availabilities = $stmtAvail->fetchAll(PDO::FETCH_ASSOC);

// luam programarile lor
$stmtApps = $db->prepare("
    SELECT a.*, ud.nume as client_nume, ud.prenume as client_prenume, st.name as session_name 
    FROM appointments a 
    LEFT JOIN user_details ud ON a.user_id = ud.user_id 
    LEFT JOIN session_types st ON a.session_type_id = st.id 
    WHERE a.staff_id = ? AND a.booking_date >= CURDATE() AND a.status NOT IN ('rejected', 'cancelled')
");
$stmtApps->execute([$staff_id]);
$apps = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

$appointments_list = [];
foreach ($apps as $app) {
    $appointments_list[$app['booking_date']][$app['start_time']] = $app;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Program: <?php echo $staff_name; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; color: #333; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn-back { background: #e9ecef; color: #495057; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        .badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 13px; }
        .badge-free { background: #d4edda; color: #155724; }
        .badge-busy { background: #f8d7da; color: #721c24; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .day-header { background: #ecf0f1; font-weight: bold; border-left: 4px solid #3498db; }
    </style>
</head>
<body>

<div class="container">
    <a href="manage_staff.php" class="btn-back">← Înapoi la Lista Staff</a>

    <h1 style="margin-bottom: 5px;">🗓️ Program de lucru</h1>
    <h3 style="color: #3498db; margin-top: 0;">Angajat: <?php echo $staff_name; ?></h3>

    <table>
        <thead>
        <tr>
            <th>Ora</th>
            <th>Stare</th>
            <th>Client</th>
            <th>Tip Antrenament</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($availabilities)): ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #777; font-style: italic;">Acest angajat nu are program de lucru setat pentru perioada următoare.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($availabilities as $day):
                $current_date = $day['available_date'];
                echo "<tr><td colspan='4' class='day-header'>📅 " . date('d.m.Y', strtotime($current_date)) .
                    " (De la " . substr($day['start_time'], 0, 5) . " la " . substr($day['end_time'], 0, 5) . ")</td></tr>";

                $start_ts = strtotime($current_date . ' ' . $day['start_time']);
                $end_ts = strtotime($current_date . ' ' . $day['end_time']);

                for ($i = $start_ts; $i < $end_ts; $i += 3600):
                    $time_db = date('H:i:s', $i);
                    $time_display = date('H:i', $i);

                    $app = isset($appointments_list[$current_date][$time_db]) ? $appointments_list[$current_date][$time_db] : null;
                    ?>
                    <tr>
                        <td><strong><?php echo $time_display; ?></strong></td>
                        <td>
                            <?php if (!$app): ?>
                                <span class="badge badge-free">LIBER</span>
                            <?php elseif ($app['status'] === 'pending'): ?>
                                <span class="badge badge-pending">CERERE PENDING</span>
                            <?php else: ?>
                                <span class="badge badge-busy">OCUPAT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $app ? $app['client_nume'] . ' ' . $app['client_prenume'] : '<span style="color:#aaa;">-</span>'; ?>
                        </td>
                        <td>
                            <?php echo $app ? '<strong>' . $app['session_name'] . '</strong>' : '<span style="color:#aaa;">-</span>'; ?>
                        </td>
                    </tr>
                <?php endfor;
            endforeach;
        endif;?>
        </tbody>
    </table>
</div>

</body>
</html>