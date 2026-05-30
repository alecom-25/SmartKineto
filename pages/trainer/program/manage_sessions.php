<?php
require_once __DIR__ . '/../../../init.php';

// Verificăm dacă e logat și are rolul corect
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['trainer', 'kineto'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$trainer_id = $_SESSION['user_id'];

// Aducem toate ședințele viitoare sau de azi
$stmt = $db->prepare("
    SELECT a.*, u.email as user_email, ud.nume, ud.prenume, st.name as session_name FROM appointments a
    JOIN users u ON a.user_id = u.id JOIN user_details ud ON u.id = ud.user_id
    JOIN session_types st ON a.session_type_id = st.id WHERE a.staff_id = ? AND a.booking_date >= CURDATE()
    ORDER BY a.booking_date ASC, a.start_time ASC");
$stmt->execute([$trainer_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Ședințe - Antrenor</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #34495e;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            border: none;
            cursor: pointer;
            display: inline-block;
            margin: 2px;
        }

        .btn-approve {
            background: #2ecc71;
        }

        .btn-cancel {
            background: #e74c3c;
        }

        .btn-reschedule {
            background: #f39c12;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .bg-pending {
            background: #f1c40f;
            color: #fff;
        }

        .bg-approved {
            background: #2ecc71;
            color: #fff;
        }

        .bg-cancelled {
            background: #e74c3c;
            color: #fff;
        }

        .bg-rescheduled {
            background: #3498db;
            color: #fff;
        }

        /* Formular ascuns pentru reprogramare */
        .reschedule-form {
            display: none;
            margin-top: 10px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .custom-msg-input {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            font-size: 12px;
            box-sizing: border-box;
            resize: vertical;
        }
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom: 20px;">
        <a href="my_schedule.php" style="text-decoration: none; color: #333; font-weight: bold;">← Înapoi la Agenda</a>
    </div>

    <h1>📅 Programările Mele</h1>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
            <?php echo $_SESSION['flash_msg'];
            unset($_SESSION['flash_msg']); ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Data și Ora</th>
            <th>Membru</th>
            <th>Tip Ședință</th>
            <th>Status</th>
            <th>Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
            <tr>
                <td>
                    <strong><?php echo date('d.m.Y', strtotime($s['booking_date'])); ?></strong><br>
                    <?php echo substr($s['start_time'], 0, 5); ?>
                </td>
                <td><?php echo $s['nume'] . ' ' . $s['prenume']; ?></td>
                <td><?php echo $s['session_name']; ?></td>
                <td><span class="badge bg-<?php echo $s['status']; ?>"><?php echo strtoupper($s['status']); ?></span>
                </td>
                <td>
                    <?php if ($s['status'] !== 'cancelled'): ?>

                        <?php if ($s['status'] === 'pending'): ?>
                            <button onclick="toggleApprove(<?php echo $s['id']; ?>, '<?php echo $s['booking_date']; ?>',
                                    '<?php echo $s['start_time']; ?>')" class="btn btn-approve">✅ Aprobă</button>

                            <form id="approve-form-<?php echo $s['id']; ?>" class="reschedule-form" action="process_session_action.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <label style="font-size:12px;">Alege Sala:</label>
                                <select name="room_id" id="room-approve-<?php echo $s['id']; ?>" required style="width:100%; padding:5px; margin-bottom:5px;">
                                    <option value="">Se caută săli...</option>
                                </select>
                                <textarea name="custom_message" class="custom-msg-input" placeholder="Mesaj opțional" rows="2"></textarea>
                                <button type="submit" class="btn btn-approve" style="width:100%;">Confirmă Aprobarea</button>
                            </form>
                        <?php endif; ?>

                        <button onclick="toggleCancel(<?php echo $s['id']; ?>)" class="btn btn-cancel">❌ Anulează</button>

                        <form id="cancel-form-<?php echo $s['id']; ?>" class="reschedule-form" action="process_session_action.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="action" value="cancel">
                            <label style="font-size:12px;">Motivul anulării:</label>
                            <textarea name="custom_message" class="custom-msg-input" placeholder="Ex: Din păcate aparatul este defect..." rows="2"></textarea>
                            <button type="submit" class="btn btn-cancel" style="width:100%;" onclick="return confirm('Confirmă anularea definitivă!');">Confirmă Anularea</button>
                        </form>

                        <button onclick="toggleReschedule(<?php echo $s['id']; ?>)" class="btn btn-reschedule" >🔄
                            Reprogramează
                        </button>

                        <form id="reschedule-form-<?php echo $s['id']; ?>" class="reschedule-form"
                              action="process_session_action.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="action" value="reschedule">
                            <input type="date" id="res-date-<?php echo $s['id']; ?>" name="new_date" min="<?php echo date('Y-m-d'); ?>" required onchange="fetchRoomsForReschedule(<?php echo $s['id']; ?>)">
                            <input type="time" id="res-time-<?php echo $s['id']; ?>" name="new_time" required onchange="fetchRoomsForReschedule(<?php echo $s['id']; ?>)">
                            <select name="room_id" id="room-reschedule-<?php echo $s['id']; ?>" required style="width:100%; padding:5px; margin-bottom:5px;">
                                <option value="">Alege întâi data și ora</option>
                            </select>
                            <textarea name="custom_message" class="custom-msg-input" placeholder="Motivul reprogramării" rows="2"></textarea>
                            <button type="submit" class="btn btn-approve" style="margin-top: 5px;">Salvează noua oră
                            </button>
                        </form>

                    <?php else: ?>
                        <em>Anulată definitiv</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleApprove(id, date, time) {
        var form = document.getElementById('approve-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            fetchRooms(date, time, 'room-approve-' + id);
        } else {
            form.style.display = 'none';
        }
    }

    function fetchRooms(date, time, selectId) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Se caută săli disponibile...</option>';

        fetch(`get_rooms_availability.php?date=${date}&time=${time}`)
            .then(res => res.json())
            .then(data => {
                select.innerHTML = '<option value="">-- Alege Sala --</option>';
                data.forEach(room => {
                    const isFull = parseInt(room.occupied) >= parseInt(room.capacity);
                    const disabled = isFull ? 'disabled' : '';
                    const statusText = isFull ? '🔴 PLIN' : `(${room.occupied}/${room.capacity} ocupat)`;

                    select.innerHTML += `<option value="${room.id}" ${disabled}>${room.name} ${statusText}</option>`;
                });
            });
    }

    function toggleReschedule(id) {
        var form = document.getElementById('reschedule-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }

    function toggleCancel(id) {
        var form = document.getElementById('cancel-form-' + id);
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }

    function fetchRoomsForReschedule(id) {
        const date = document.getElementById('res-date-' + id).value;
        const time = document.getElementById('res-time-' + id).value;
        if(date && time) {
            fetchRooms(date, time, 'room-reschedule-' + id);
        }
    }
</script>

</body>
</html>