<?php
require_once __DIR__ . '/../../../init.php';

// Verificăm dacă e logat și are rolul corect (presupunem că doar trainer/kineto au acces)
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
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom: 20px;">
        <a href="../../../dashboard.php" style="text-decoration: none; color: #333; font-weight: bold;">← Înapoi la
            Dashboard</a>
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
                            <a href="process_session_action.php?id=<?php echo $s['id']; ?>&action=approve"
                               class="btn btn-approve">✅ Aprobă</a>
                        <?php endif; ?>

                        <a href="process_session_action.php?id=<?php echo $s['id']; ?>&action=cancel"
                           class="btn btn-cancel" onclick="return confirm('Sigur vrei să anulezi?');">❌ Anulează</a>

                        <button onclick="toggleReschedule(<?php echo $s['id']; ?>)" class="btn btn-reschedule" >🔄
                            Reprogramează
                        </button>

                        <form id="form-<?php echo $s['id']; ?>" class="reschedule-form"
                              action="process_session_action.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="action" value="reschedule">
                            <input type="date" name="new_date" min="<?php echo date('Y-m-d'); ?>" required>
                            <input type="time" name="new_time" required>
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
    function toggleReschedule(id) {
        var form = document.getElementById('form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>

</body>
</html>