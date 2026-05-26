<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

//verificam daca este deschisa acum
$zi_curenta = date('N'); // 1 = luni, 7 = duminică
$ora_curenta = (int)date('H');

$este_deschis = false;

if ($zi_curenta >= 1 && $zi_curenta <= 6) {
    // Luni - Sambata: 08:00 - 20:00
    if ($ora_curenta >= 8 && $ora_curenta < 20) {
        $este_deschis = true;
    }
} elseif ($zi_curenta == 7) {
    // Duminica: 11:00 - 18:00
    if ($ora_curenta >= 11 && $ora_curenta < 18) {
        $este_deschis = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Orar Sală - SmartKineto</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 40px 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; }

        .btn-back { display: inline-block; padding: 10px 20px; background: #e9ecef; color: #495057; text-decoration: none; border-radius: 8px; font-weight: bold; margin-bottom: 30px; transition: 0.3s; }
        .btn-back:hover { background: #dee2e6; }

        h1 { color: #2c3e50; margin-bottom: 10px; }
        .subtitle { color: #7f8c8d; margin-bottom: 30px; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 10px 20px; border-radius: 50px; font-weight: bold; font-size: 16px; margin-bottom: 30px; }
        .status-open { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-closed { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Tabelul cu programul */
        .schedule-box { text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 30px; }
        .schedule-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px dashed #cbd5e1; font-size: 18px; }
        .schedule-row:last-child { border-bottom: none; }
        .day { font-weight: bold; color: #34495e; }
        .hours { color: #3498db; font-weight: bold; }

        .today { background: #e0f2fe; margin: -10px -20px; padding: 10px 20px; border-radius: 8px; border-bottom: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container">
    <div style="text-align: left;">
        <a href="../../dashboard.php" class="btn-back">← Înapoi la Dashboard</a>
    </div>

    <h1>🕒 Programul Sălii</h1>
    <p class="subtitle">Te așteptăm la antrenament în intervalele de mai jos!</p>

    <?php if ($este_deschis): ?>
        <div class="status-badge status-open">🟢 ACUM ESTE DESCHIS</div>
    <?php else: ?>
        <div class="status-badge status-closed">🔴 ACUM ESTE ÎNCHIS</div>
    <?php endif; ?>

    <div class="schedule-box">
        <div class="schedule-row <?php echo ($zi_curenta >= 1 && $zi_curenta <= 5) ? 'today' : ''; ?>">
            <span class="day">Luni - Vineri</span>
            <span class="hours">08:00 - 20:00</span>
        </div>

        <div class="schedule-row <?php echo ($zi_curenta == 6) ? 'today' : ''; ?>">
            <span class="day">Sâmbătă</span>
            <span class="hours">08:00 - 20:00</span>
        </div>

        <div class="schedule-row <?php echo ($zi_curenta == 7) ? 'today' : ''; ?>">
            <span class="day">Duminică</span>
            <span class="hours">11:00 - 18:00</span>
        </div>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #94a3b8;">
        * În zilele de sărbătoare legală, programul poate suferi modificări. Te rugăm să verifici avizierul sălii.
    </p>
</div>

</body>
</html>