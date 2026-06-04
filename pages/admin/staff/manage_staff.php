<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];

    $checkApps = $db->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id = ? OR user_id = ?");
    $checkApps->execute([$id_to_delete, $id_to_delete]);
    $apps_count = $checkApps->fetchColumn();

    $checkPacients = $db->prepare("SELECT COUNT(*) FROM patient_medical_records WHERE therapist_id = ? OR patient_id = ?");
    $checkPacients->execute([$id_to_delete, $id_to_delete]);
    $pacients_count = $checkPacients->fetchColumn();

    if ($apps_count > 0 || $pacients_count > 0) {
        $_SESSION['admin_msg'] = "Nu poți șterge acest utilizator deoarece are $apps_count programări în istoric și $pacients_count înregistrări asociate!";
    } else {
        $db->prepare("DELETE FROM subscriptions WHERE user_id = ?")->execute([$id_to_delete]);
        $db->prepare("DELETE FROM user_details WHERE user_id = ?")->execute([$id_to_delete]);
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id_to_delete]);

        $_SESSION['admin_msg'] = "Utilizatorul a fost șters cu succes din sistem!";
    }

    header("Location: manage_staff.php");
    exit();
}

$stmt = $db->query("SELECT u.id, u.email, u.role, ud.nume, ud.prenume, s.tier FROM users u 
    JOIN user_details ud ON u.id = ud.user_id 
    LEFT JOIN subscriptions s ON u.id = s.user_id 
    WHERE u.role IN ('trainer', 'kineto', 'member') 
    ORDER BY u.role ASC, ud.nume ASC");
$user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Utilizatori - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
        }

        .btn-csv {
            background: #27ae60;
        }

        .btn-xml {
            background: #e67e22;
        }

        .btn-import {
            background: #34495e;
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
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .bg-kineto {
            background: #9b59b6;
            color: white;
        }

        .bg-trainer {
            background: #3498db;
            color: white;
        }

        .bg-member {
            background: #2ecc71;
            color: white;
        }

        .import-box {
            background: #f1f3f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../../../dashboard.php" class="btn btn-back" style="margin-bottom: 15px;">← Înapoi la Dashboard</a>

    <div class="header-actions">
        <h1 style="margin: 0;">️ Gestiune Staff și Membri</h1>
        <div style="display: flex; gap: 10px;">
            <a href="export/export_staff_csv.php" class="btn btn-csv">⬇️ Export CSV</a>
            <a href="export/export_staff_xml.php" class="btn btn-xml">⬇️ Export XML</a>
        </div>
    </div>

    <?php if (isset($_SESSION['admin_msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
            <?php echo $_SESSION['admin_msg'];
            unset($_SESSION['admin_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="import-box">
        <strong style="margin-right: 10px;">⬆️ Importă Utilizator nou:</strong>
        <form action="import/import_staff.php" method="POST" enctype="multipart/form-data"
              style="display: flex; gap: 10px; align-items: center; margin: 0;">
            <input type="file" name="file_upload" accept=".csv, .xml" required
                   style="border: 1px solid #ccc; padding: 5px; border-radius: 4px; background: white;">
            <button type="submit" class="btn btn-import">Încarcă Fișierul</button>
        </form>
    </div>

    <table>
        <thead>
        <tr>
            <th> ID</th>
            <th> Nume si Prenume</th>
            <th> Email</th>
            <th> Rol</th>
            <th> Abonament</th>
            <th> Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($user_list)): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#777;">Nu există utilizatori înregistrați.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($user_list as $s): ?>
                <tr>
                    <td><?php echo $s['id']; ?></td>
                    <td><strong><?php echo $s['nume'] . ' ' . $s['prenume']; ?></strong></td>
                    <td><?php echo $s['email']; ?></td>
                    <td>
                            <span class="badge <?php echo $s['role'] === 'kineto' ? 'bg-kineto' : ($s['role'] === 'trainer' ? 'bg-trainer' : 'bg-member'); ?>">
                                <?php echo $s['role'] === 'kineto' ? 'Kinetoterapie' : ($s['role'] === 'trainer' ? 'Fitness' : 'Membru'); ?>
                            </span>
                    </td>
                    <td>
                        <?php echo $s['role'] === 'member' ? (!empty($s['tier']) ? strtoupper($s['tier']) : 'FĂRĂ ABONAMENT') : '-'; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <?php if ($s['role'] !== 'member'): ?>
                                <a href="view_staff_schedule.php?id=<?php echo $s['id']; ?>" class="btn"
                                   style="background: #3498db; color: white;">📅 Vezi Program</a>
                            <?php endif; ?>
                            <a href="manage_staff.php?delete_id=<?php echo $s['id']; ?>" class="btn"
                               style="background: #e74c3c; color: white;"
                               onclick="return confirm('Sigur vrei să ștergi acest utilizator? Acțiunea este ireversibilă!');">🗑️
                                Șterge</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>