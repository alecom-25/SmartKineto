<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

// statistica utilizatori
$stmtUsers = $db->query("SELECT role, COUNT(id) as total FROM users GROUP BY role");
$usersData = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// statistica pentru staff -> cine are cele mai multe ședințe programate
$stmtTopStaff = $db->query("SELECT ud.nume, ud.prenume, COUNT(a.id) as total_sessions FROM appointments a 
    JOIN user_details ud ON a.staff_id = ud.user_id GROUP BY a.staff_id ORDER BY total_sessions DESC LIMIT 5");
$topStaffData = $stmtTopStaff->fetchAll(PDO::FETCH_ASSOC);

// statistici abonamente -> după tipul abonamentului din istoricul de plati/abonamente
$toate_abonamentele = ['fitness' => 0, 'forta' => 0, 'kineto' => 0, 'tip1' => 0, 'tip2' => 0,
        'tip3' => 0, 'vip' => 0];
// extragem doar abonamentele active și le grupăm după nivelul lor
$stmtSubs = $db->query("SELECT tier, has_fitness, has_forta, has_kineto FROM subscriptions WHERE is_suspended = 0");
$subsData = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

foreach ($subsData as $row) {
    if ($row['tier'] == 'membru') {
        if ($row['has_fitness'] == 1) {
            $toate_abonamentele['fitness'] += 1;
        } elseif ($row['has_forta'] == 1) {
            $toate_abonamentele['forta'] += 1;
        } elseif ($row['has_kineto'] == 1) {
            $toate_abonamentele['kineto'] += 1;
        }
    } elseif ($row['tier'] == 'premium') {
        if ($row['has_fitness'] == 1 && $row['has_forta'] == 1) {
            $toate_abonamentele['tip1'] += 1;
        } elseif ($row['has_fitness'] == 1 && $row['has_kineto'] == 1) {
            $toate_abonamentele['tip2'] += 1;
        } elseif ($row['has_fitness'] == 0) {
            $toate_abonamentele['tip3'] += 1;
        }
    } else {
        $toate_abonamentele['vip'] += 1;
    }
}

// statistica sesiuni
$stmtDay = $db->query("SELECT COUNT(*) FROM appointments WHERE booking_date = CURDATE() AND status != 'cancelled'");
$sessionsDay = $stmtDay->fetchColumn();

$stmtWeek = $db->query("SELECT COUNT(*) FROM appointments WHERE YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE(), 1) AND status != 'cancelled'");
$sessionsWeek = $stmtWeek->fetchColumn();

$stmtMonth = $db->query("SELECT COUNT(*) FROM appointments WHERE MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND status != 'cancelled'");
$sessionsMonth = $stmtMonth->fetchColumn();

$sessionsData = [
        'Azi' => $sessionsDay,
        'Săptămâna curentă' => $sessionsWeek,
        'Luna curentă' => $sessionsMonth
];

// Transformăm datele PHP în JSON pentru a le putea folosi în JavaScript
$usersJson = json_encode($usersData);
$topStaffJson = json_encode($topStaffData);
$subsJson = json_encode($toate_abonamentele);
$sessionsJson = json_encode($sessionsData);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Statistici și Rapoarte - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-export {
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
            background: #27ae60;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .export-img-btns {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .btn-img {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #34495e;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../../../dashboard.php" class="btn-back">← Înapoi la Dashboard</a>

    <div class="header-actions">
        <h1 style="margin: 0;">📊 Statistici și Rapoarte</h1>
        <div style="display: flex; gap: 10px;">
            <a href="export_stats_csv.php" class="btn-export">⬇️ Export de Date in CSV</a>
            <a href="export_stats_xml.php" class="btn-export" style="background: #e67e22;">⬇️ Export de Date in XML</a>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>👥 Repartiție Utilizatori Activi</h3>
            <canvas id="usersChart"></canvas>
            <div class="export-img-btns">
                <button class="btn-img" onclick="downloadChart('usersChart', 'png')">Descarcă PNG</button>
                <button class="btn-img" onclick="downloadChart('usersChart', 'webp')">Descarcă WebP</button>
            </div>
        </div>

        <div class="card">
            <h3>🏆 Top Antrenori / Terapeuți </h3>
            <canvas id="staffChart"></canvas>
            <div class="export-img-btns">
                <button class="btn-img" onclick="downloadChart('staffChart', 'png')">Descarcă PNG</button>
                <button class="btn-img" onclick="downloadChart('staffChart', 'webp')">Descarcă WebP</button>
            </div>
        </div>

        <div class="card">
            <h3>💳 Distribuție Tipuri Abonamente</h3>
            <canvas id="subsChart"></canvas>
            <div class="export-img-btns">
                <button class="btn-img" onclick="downloadChart('subsChart', 'png')">Descarcă PNG</button>
                <button class="btn-img" onclick="downloadChart('subsChart', 'webp')">Descarcă WebP</button>
            </div>
        </div>

        <div class="card">
            <h3>📅 Volum Programări Rezervate</h3>
            <canvas id="sessionsChart"></canvas>
            <div class="export-img-btns">
                <button class="btn-img" onclick="downloadChart('sessionsChart', 'png')">Descarcă PNG</button>
                <button class="btn-img" onclick="downloadChart('sessionsChart', 'webp')">Descarcă WebP</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Preluăm datele JSON din PHP
    const usersData = <?php echo $usersJson; ?>;
    const topStaffData = <?php echo $topStaffJson; ?>;
    const subsData = <?php echo $subsJson; ?>;
    const sessionsData = <?php echo $sessionsJson; ?>;

    // setari pentru utilizatori
    const usersLabels = usersData.map(u => u.role.toUpperCase());
    const usersCounts = usersData.map(u => u.total);

    new Chart(document.getElementById('usersChart'), {
        type: 'doughnut',
        data: {
            labels: usersLabels,
            datasets: [{
                data: usersCounts,
                backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f']
            }]
        }
    });

    // setari grafica pt staff
    const staffLabels = topStaffData.map(s => s.nume + ' ' + s.prenume);
    const staffCounts = topStaffData.map(s => s.total_sessions);

    new Chart(document.getElementById('staffChart'), {
        type: 'bar',
        data: {
            labels: staffLabels,
            datasets: [{
                label: 'Număr Ședințe Programate',
                data: staffCounts,
                backgroundColor: '#9b59b6'
            }]
        },
        options: {scales: {y: {beginAtZero: true, ticks: {stepSize: 1}}}}
    });

    // setari grafica abonamente
    const subsLabels = Object.keys(subsData).map(k => k.toUpperCase());
    const subsCounts = Object.values(subsData);

    new Chart(document.getElementById('subsChart'), {
        type: 'pie',
        data: {
            labels: subsLabels,
            datasets: [{
                data: subsCounts,
                backgroundColor: ['#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#f39c12', '#d35400', '#8e44ad']
            }]
        }
    });

    //setari grafica sesiuni
    new Chart(document.getElementById('sessionsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(sessionsData),
            datasets: [{
                label: 'Sesiuni Programate',
                data: Object.values(sessionsData),
                backgroundColor: ['#f1c40f', '#e67e22', '#e74c3c']
            }]
        },
        options: {scales: {y: {beginAtZero: true, ticks: {stepSize: 1}}}}
    });

    // functie pt exportul paginilor (png si webp)
    function downloadChart(canvasId, format) {
        const canvas = document.getElementById(canvasId);
        // transformam graficul intr-ul URL descarcabil
        const imageURL = canvas.toDataURL(`image/${format}`, 1.0);

        // cream un link invizibil care descarca si il accesam automat
        const link = document.createElement('a');
        link.download = `statistica_${canvasId}.${format}`;
        link.href = imageURL;
        link.click();
    }
</script>

</body>
</html>