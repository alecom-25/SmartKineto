<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'trainer') {
//     die("Acces interzis");
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$sql = "SELECT p.*, u.username FROM pending_upgrades p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$all_requests = $db->query($sql)->fetchAll();
?>

<!DOCTYPE>
<html>
<head>
    <style>
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #758382;
            color: white !important;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background-color: #505958;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
<h2>Istoric Complet Cereri Abonamente</h2>
<a href="manage_payments.php" class="btn">← Înapoi la Cereri</a>
<table>
    <thead>
    <tr>
        <th>Data</th>
        <th>Membru</th>
        <th>Plan Solicitat</th>
        <th>Sumă</th>
        <th>Status Final</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($all_requests as $req): ?>
        <tr>
            <td><?php echo $req['created_at']; ?></td>
            <td><?php echo $req['username']; ?></td>
            <td><?php echo strtoupper($req['new_tier']); ?></td>
            <td><?php echo $req['amount_to_pay']; ?> RON</td>
            <td>
                    <span class="badge-<?php echo $req['status']; ?>">
                        <?php
                        if ($req['status'] == 'approved') echo "✅ Aprobat";
                        elseif ($req['status'] == 'rejected') echo "❌ Respins";
                        else echo "⏳ În așteptare";
                        ?>
                    </span>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>