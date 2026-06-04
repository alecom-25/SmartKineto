<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['trainer', 'kineto'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$trainer_id = $_SESSION['user_id'];

// adaugare interval liber
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slot'])) {
    $date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $stop_time = $_POST['stop_time'];

    if (!empty($date) && !empty($start_time) && !empty($stop_time)) {
        // Verificăm dacă intervalul există deja ca să nu îl duplicăm
        $check = $db->prepare("SELECT id FROM staff_availability WHERE trainer_id = ? AND available_date = ? AND start_time = ? AND end_time = ?");
        $check->execute([$trainer_id, $date, $start_time, $stop_time]);

        if ($check->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO staff_availability (trainer_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$trainer_id, $date, $start_time, $stop_time]);
            $_SESSION['schedule_msg'] = " Intervalul de lucru a fost adăugat cu succes!";
        } else {
            $_SESSION['schedule_msg'] = " Ai adăugat deja acest interval de lucru.";
        }
    }
    header("Location: my_schedule.php");
    exit();
}

$stmtAvail = $db->prepare("SELECT available_date, start_time, end_time as stop_time FROM staff_availability 
                                WHERE trainer_id = ? AND available_date >= CURDATE() ORDER BY available_date ASC");
$stmtAvail->execute([$trainer_id]);
$my_schedule = $stmtAvail->fetchAll(PDO::FETCH_ASSOC);

// luam toate programarile antrenorului
$stmtApps = $db->prepare("
    SELECT a.*, ud.nume as client_nume, ud.prenume as client_prenume, st.name as session_name, r.name as room_name FROM appointments a 
    LEFT JOIN user_details ud ON a.user_id = ud.user_id LEFT JOIN session_types st ON a.session_type_id = st.id 
    LEFT JOIN rooms r ON a.room_id = r.id 
    WHERE a.staff_id = ? AND a.booking_date >= CURDATE() AND a.status NOT IN ('rejected', 'cancelled')
");
$stmtApps->execute([$trainer_id]);
$apps = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

// grupam programarile dupa data si ora
$appointments_list = [];
foreach ($apps as $app) {
    $appointments_list[$app['booking_date']][$app['start_time']] = $app;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Programul Meu - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        .btn-blue {
            background: #3498db;
            color: white;
        }

        .btn-blue:hover {
            background: #2980b9;
        }

        .btn-green {
            background: #2ecc71;
            color: white;
        }

        .btn-green:hover {
            background: #27ae60;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
        }

        .form-box {
            background: #f1f3f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: none;
        }

        .form-box.active {
            display: block;
        }

        .close-notif {
            text-decoration: none;
            color: inherit;
            font-weight: bold;
            font-size: 20px;
            text-align: left;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input[type="date"], input[type="time"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #34495e;
            color: white;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 13px;
        }

        .badge-free {
            background: #d4edda;
            color: #155724;
        }

        .badge-busy {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../../../dashboard.php" class="btn btn-back">← Dashboard</a>

    <h1>🗓️ Gestiune Program de Lucru</h1>

    <?php if (isset($_SESSION['schedule_msg'])): ?>
        <div style="background: #e3f2fd; color: #0d47a1; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
            <?php echo $_SESSION['schedule_msg']; ?>
            <!-- &times deseneaza x, face sa poti sa dai click pe x; link-ul/div-ul care il contine/ascunde-l/ramane pagina unde era inainte-->
            <a href="#" onclick="this.parentElement.style.display='none'; return false;" class="close-notif">&times;</a>
        </div>
        <?php unset($_SESSION['schedule_msg']); ?>
    <?php endif; ?>

    <div class="nav-buttons">
        <button class="btn btn-blue" onclick="toggleForm()">➕ Creează un Interval Liber</button>
        <a href="manage_sessions.php" class="btn btn-green">📩 Vezi Cererile de Sesiuni</a>
    </div>

    <div id="add_slot_form" class="form-box">
        <h3>Adaugă o nouă oră de disponibilitate în calendar</h3>
        <form method="POST" action="">
            <label for="available_date">Alege ziua:</label>
            <input type="date" id="available_date" name="available_date" min="<?php echo date('Y-m-d'); ?>" required>

            <label for="start_time">Alege ora de start:</label>
            <input type="time" id="start_time" name="start_time" required>

            <label for="stop_time">Alege ora de terminare:</label>
            <input type="time" id="stop_time" name="stop_time" required>

            <button type="submit" name="add_slot" class="btn btn-blue" style="width: 100%; margin-top: 15px;">Salvează
                Intervalul ca Disponibil
            </button>
        </form>
    </div>

    <h2>📋 Agenda mea</h2>
    <table>
        <thead>
        <tr>
            <th>Data și Ora</th>
            <th>Stare</th>
            <th>Client</th>
            <th>Tip Antrenament</th>
            <th>Sala</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($my_schedule)): ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #777; font-style: italic;">Nu ai adăugat încă niciun
                    interval orar de lucru în calendar.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($my_schedule as $day):
                echo "<tr><td colspan='4' class='day-header'>📅 " . date('d.m.Y', strtotime($day['available_date'])) .
                        " (De la " . substr($day['start_time'], 0, 5) . " la " . substr($day['stop_time'], 0, 5) . ")</td></tr>";

                $start_ts = strtotime($day['available_date'] . ' ' . $day['start_time']);
                $end_ts = strtotime($day['available_date'] . ' ' . $day['stop_time']);

                for ($i = $start_ts; $i < $end_ts; $i += 3600):
                    $time_db = date('H:i:s', $i);
                    $time_display = date('H:i', $i);
                    $app = isset($appointments_list[$day['available_date']][$time_db]) ? $appointments_list[$day['available_date']][$time_db] : null; ?>
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
                            <?php
                            if (!empty($app['client_nume'])) {
                                echo $app['client_nume'] . ' ' . $app['client_prenume'];
                            } else {
                                echo '<span style="color:#aaa;">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($app['session_name'])) {
                                echo '<strong>' . $app['session_name'] . '</strong>';
                            } else {
                                echo '<span style="color:#aaa;">Nicio rezervare</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($app && !empty($app['room_name'])) {
                                echo '<span style="color:#e67e22; font-weight:bold;">📍 ' . $app['room_name'] . '</span>';
                            } else {
                                echo '<span style="color:#aaa;">-</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endfor;
            endforeach;
        endif; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleForm() {
        var form = document.getElementById('add_slot_form');
        form.classList.toggle('active');
    }
</script>

</body>
</html>