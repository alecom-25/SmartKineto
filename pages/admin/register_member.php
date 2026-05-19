<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['optiuni'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $data_nasterii = $_POST['data_nasterii'];
    $judet = $_POST['judet'];
    $oras = $_POST['oras'];
    $adresa = $_POST['adresa'];
    $telefon = $_POST['telefon'];

    $db->beginTransaction();
    try {
        $stmtUser = $db->prepare("INSERT INTO users (email, username, password_hash, role) VALUES (?, ?, NULL, ?)");
        $stmtUser->execute([$email, $username, $role]);
        $new_user_id = $db->lastInsertId();

        $stmtDetails = $db->prepare("INSERT INTO user_details (user_id, nume, prenume, data_nasterii, judet, oras, adresa, telefon) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtDetails->execute([$new_user_id, $nume, $prenume, $data_nasterii, $judet, $oras, $adresa, $telefon]);

        $db->commit();
        echo "Utilizator creat! Membrul se poate loga acum folosind email-ul pentru a-și seta parola.";
    } catch (Exception $e) {
        $db->rollBack();
        die("Eroare: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title> Inregistrare membru</title>
        <style>
            .form-container {
                width: 40%;
                background: #418ed6;
                padding: 30px;
                border-radius: 12px;
                border: 1px solid #333;
            }
            input {
                width: 100%;
                padding: 12px;
                margin: 10px 0 20px 0;
                background: #ffffff;
                border: 1px solid #444;
                color: black;
                border-radius: 4px;
                box-sizing: border-box;
            }

        </style>
    </head>
    <body>
        <h1> Inregistrare utilizator: </h1>
        <div class="form-container" style="display: flex; gap: 40px;">
            <form method="POST">
                <label> Rol:</label>
                <select name="optiuni">
                    <option value="member">Membru</option>
                    <option value="trainer">Trainer</option>
                    <option value="kineto">Kineto-terapeut</option>
                    <option value="admin"> Admin</option>
                </select>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="nume" placeholder="Nume" required>
                <input type="text" name="prenume" placeholder="Prenume" required>
                <label> Data Nasterii</label>
                <input type="date" name="data_nasterii" placeholder="Data Nasterii" required>
                <input type="text" name="judet" placeholder="Judet" required>
                <input type="text" name="oras" placeholder="Oras" required>
                <input type="text" name="adresa" placeholder="Adresa" required>
                <input type="text" name="telefon" placeholder="Telefon" required>
                <button type="submit">Înregistrează Membru</button>
            </form>
        </div>

        <a href="manage_users.php" style="color: #3498db; text-decoration: none;">← Înapoi la Manage Users</a>
    </body>
</html>