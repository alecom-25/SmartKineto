<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kineto') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$therapist_id = $_SESSION['user_id'];

// Dacă formularul e trimis, preluăm pacientul în listă
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_patient_id'])) {
    $patient_id = $_POST['new_patient_id'];

    // Inserăm un rând gol în fișa medicală, ca să apară în "Lista mea"
    $stmt = $db->prepare("INSERT IGNORE INTO patient_medical_records (patient_id, therapist_id, diagnosis, therapist_notes) VALUES (?, ?, '', '')");
    $stmt->execute([$patient_id, $therapist_id]);

    $_SESSION['client_msg'] = " Pacientul a fost adăugat cu succes în lista ta!";
    header("Location: my_clients.php");
    exit();
}

// Căutăm membrii care nu sunt deja pacienții acestui terapeut
$stmt = $db->prepare("SELECT u.id, ud.nume, ud.prenume, u.email FROM users u
    JOIN user_details ud ON u.id = ud.user_id WHERE u.role = 'member' 
      AND u.id NOT IN (SELECT patient_id FROM patient_medical_records WHERE therapist_id = ?) ORDER BY ud.nume ASC");
$stmt->execute([$therapist_id]);
$available_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adauga pacient - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        select {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: white;
            background: #2ecc71;
            width: 100%;
            font-size: 16px;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
            text-decoration: none;
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="my_clients.php" class="btn-back">← Înapoi la Pacienți</a>
    <h2>Adaugă un nou pacient</h2>
    <p>Alege un membru înregistrat în sistem pentru a-i deschide o fișă medicală și a-l programa la proceduri.</p>

    <form method="POST">
        <select name="new_patient_id" required>
            <option value="">-- Caută pacient... --</option>
            <?php foreach ($available_patients as $p): ?>
                <option value="<?php echo $p['id']; ?>">
                    <?php echo $p['nume'] . ' ' . $p['prenume'] . ' (' . $p['email'] . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Preia Pacientul</button>
    </form>
</div>
</body>
</html>