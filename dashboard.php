<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$id = $_SESSION['user_id'];

$stmt = $db->query("SELECT COUNT(*) FROM pending_upgrades WHERE status = 'pending'");
$nr_cereri = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id = ? AND status = 'pending'");
$stmt->execute([$id]);
$nr_sessions = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .welcome-section {
            margin-top: 50px;
            text-align: center;
        }

        .welcome-section h1 {
            color: #2c3e50;
            font-size: 2.5em;
        }

        .role-badge {
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            width: 80%;
            max-width: 1000px;
            margin-top: 40px;
        }

        .card-btn {
            background: white;
            padding: 30px 20px;
            text-align: center;
            text-decoration: none;
            color: #34495e;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
        }

        .card-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            background-color: #ebf5fb;
        }

        .icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .logout-btn {
            margin-top: 50px;
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        .admin-card {
            border-left: 5px solid #e74c3c;
        }

        .staff-card {
            border-left: 5px solid #f1c40f;
        }
    </style>
</head>
<body>

<div class="welcome-section">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <span class="role-badge"><?php echo ucfirst($role); ?></span>
</div>

<div class="grid-container">
    <?php if ($role === 'member'): ?>
        <a href="pages/personal_information.php" class="card-btn">
            <span class="icon">👤</span>
            <span>Personal Information</span>
        </a>
        <a href="pages/member/istoric/history.php" class="card-btn">
            <span class="icon">📜</span>
            <span>History & Activity</span>
        </a>
        <a href="pages/member/orar.php" class="card-btn">
            <span class="icon">📅</span>
            <span>Program Sala</span>
        </a>
        <a href="pages/member/sessions/appointments.php" class="card-btn">
            <span class="icon">🎟️</span>
            <span>Sessions</span>
        </a>
        <a href="pages/member/abonament/abonament.php" class="card-btn">
            <span class="icon">💳</span>
            <span>Abonament</span>
        </a>
    <?php endif; ?>

    <?php if ($role === 'trainer' || $role === 'kineto'): ?>
        <a href="pages/personal_information.php" class="card-btn staff-card">
            <span class="icon">👤</span>
            <span>Personal Information</span>
        </a>
        <?php if ($role === 'trainer'): ?>
            <a href="pages/trainer/cereri_abonamente/manage_payments.php" class="card-btn staff-card">
                <span class="icon">📩</span>
                <span>Cereri abonamente</span>
                <?php if ($nr_cereri > 0): ?>
                    <span style="background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 12px;">
                        <?php echo $nr_cereri; ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <?php if ($role === 'kineto'): ?>
            <a href="pages/kineto/clienti/my_clients.php" class="card-btn staff-card">
                <span class="icon">👥</span>
                <span>Clienții mei</span>
            </a>
        <?php endif; ?>
        <a href="pages/trainer/program/my_schedule.php" class="card-btn staff-card">
            <span class="icon">🕒</span>
            <span>Programul meu</span>
            <?php if ($nr_sessions > 0): ?>
                <span style="background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 12px;">
                        <?php echo $nr_sessions; ?>
                    </span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
        <a href="pages/admin/useri/register_member.php" class="card-btn admin-card">
            <span class="icon">👤️</span>
            <span>Adaugă un utilizator</span>
        </a>
        <a href="pages/admin/sali/manage_rooms.php" class="card-btn admin-card">
            <span class="icon">🏙</span>
            <span>Gestionare Săli</span>
        </a>
        <a href="pages/admin/staff/manage_staff.php" class="card-btn admin-card">
            <span class="icon">👥️</span>
            <span>Gestionare Staff</span>
        </a>
        <a href="pages/admin/rapoarte/statistics.php" class="card-btn admin-card">
            <span class="icon">📊</span>
            <span>Rapoarte Finale</span>
        </a>
    <?php endif; ?>
</div>

<a href="logout.php" class="logout-btn">Logout System</a>

</body>
</html>