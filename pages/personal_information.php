<?php
require_once __DIR__ . '/../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$current_user_id = $_SESSION['user_id'];

$query = "SELECT u.email, u.role, d.nume, d.prenume, d.data_nasterii, d.judet, d.oras, d.adresa, d.telefon 
          FROM users u 
          LEFT JOIN user_details d ON u.id = d.user_id 
          WHERE u.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$current_user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Informații Personale - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .info-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .label {
            font-weight: bold;
            color: #7f8c8d;
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .value {
            display: block;
            color: #2c3e50;
            margin-top: 5px;
        }

        .back-link {
            display: block;
            margin-top: 30px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>Informații Personale</h2>

    <div class="info-grid">
        <div class="info-item">
            <span class="label">Nume</span>
            <span class="value">
                <?php echo isset($userData['nume']) ? htmlspecialchars($userData['nume']) : 'Nesetat'; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="label">Prenume</span>
            <span class="value">
                <?php echo isset($userData['prenume']) ? htmlspecialchars($userData['prenume']) : 'Nesetat'; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="label">Email</span>
            <span class="value"><?php echo htmlspecialchars($userData['email']); ?></span>
        </div>

        <div class="info-item">
            <span class="label">Telefon</span>
            <span class="value">
                <?php echo isset($userData['telefon']) ? htmlspecialchars($userData['telefon']) : '-'; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="label">Județ</span>
            <span class="value">
                <?php echo isset($userData['judet']) ? htmlspecialchars($userData['judet']) : '-'; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="label">Localitate</span>
            <span class="value">
                <?php echo isset($userData['oras']) ? htmlspecialchars($userData['oras']) : '-'; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="label">Data Nașterii</span>
            <span class="value">
                <?php echo isset($userData['data_nasterii']) ? htmlspecialchars($userData['data_nasterii']) : '-'; ?>
            </span>
        </div>
    </div>

    <a href="../dashboard.php" class="back-link">← Înapoi la Dashboard</a>
</div>

</body>
</html>
