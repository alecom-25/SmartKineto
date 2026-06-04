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
$filter = isset($_GET['type']) ? $_GET['type'] : 'all';

if ($filter === 'all') {
    $query = "SELECT * FROM activities_history WHERE user_id = ? ORDER BY created_at DESC";
    $params = [$user_id];
} else {
    $query = "SELECT * FROM activities_history WHERE user_id = ? AND activity_type = ? ORDER BY created_at DESC";
    $params = [$user_id, $filter];
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Istoric Activitate - SmartKineto</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #3498db;
            border-radius: 20px;
            text-decoration: none;
            color: #3498db;
            transition: 0.3s;
        }

        .filter-btn.active {
            background: #3498db;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #777;
            text-transform: uppercase;
            font-size: 0.8em;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .type-access {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-purchase {
            background: #f1f8e9;
            color: #388e3c;
        }

        .type-session {
            background: #fff3e0;
            color: #f57c00;
        }

        .type-payment {
            background: #f3e5f5;
            color: #7b1fa2;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Istoric Activitate</h2>

    <div class="filters">
        <a href="history.php?type=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">Toate</a>
        <a href="history.php?type=access"
           class="filter-btn <?php echo $filter === 'access' ? 'active' : ''; ?>">Intrari</a>
        <a href="history.php?type=purchase" class="filter-btn <?php echo $filter === 'purchase' ? 'active' : ''; ?>">Cumparaturi</a>
        <a href="history.php?type=session" class="filter-btn <?php echo $filter === 'session' ? 'active' : ''; ?>">Sedinte</a>
        <a href="history.php?type=payment" class="filter-btn <?php echo $filter === 'payment' ? 'active' : ''; ?>">Abonamente</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Tip</th>
            <th>Descriere</th>
            <th>Suma</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($activities) > 0): ?>
            <?php foreach ($activities as $act): ?>
                <tr>
                    <td><?php echo date('d.m.Y H:i', strtotime($act['created_at'])); ?></td>
                    <td>
                        <span class="type-badge type-<?php echo $act['activity_type']; ?>"><?php echo ucfirst($act['activity_type']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($act['description']); ?></td>
                    <td><?php echo $act['amount'] > 0 ? $act['amount'] . ' RON' : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Nu am gasit nicio activitate pentru acest filtru.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="../../../dashboard.php" style="color: #3498db; text-decoration: none;">← Înapoi la Dashboard</a>
</div>

</body>
</html>