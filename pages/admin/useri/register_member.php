<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
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
        $mesaj_confirmare = "Utilizator creat! Membrul se poate loga acum folosind email-ul pentru a-și seta parola.";
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
            <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { background-color: #f4f7f6; padding: 40px 20px; display: flex; justify-content: center; }

            .form-card {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                width: 100%;
                max-width: 600px;
                border-top: 5px solid #e74c3c; /* Culoarea roșie pe care o ai deja la admin */
            }
            .form-header { margin-bottom: 30px; }
            .form-header h2 { color: #2c3e50; font-size: 1.8rem; margin-bottom: 5px; }
            .form-header p { color: #7f8c8d; font-size: 0.95rem; }

            .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 500px) { .form-grid { grid-template-columns: 1fr; } }

            .form-group { margin-bottom: 20px; text-align: left; }
            .form-group.full-width { grid-column: span 2; }
            @media (max-width: 500px) { .form-group.full-width { grid-column: span 1; } }

            .form-group label { display: block; margin-bottom: 8px; color: #34495e; font-weight: 500; font-size: 0.9rem; }
            .form-group input, .form-group select {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 0.95rem;
                color: #2c3e50;
                background: #fdfdfd;
                transition: all 0.3s;
            }
            .form-group input:focus, .form-group select:focus {
                border-color: #e74c3c;
                outline: none;
                box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
                background: white;
            }

            .btn-container { margin-top: 20px; display: flex; gap: 15px; justify-content: flex-end; }
            .btn-submit { background: #e74c3c; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
            .btn-submit:hover { background: #c0392b; }
            .btn-back { background: #95a5a6; color: white; padding: 12px 24px; border: none; border-radius: 6px; text-decoration: none; font-weight: bold; text-align: center; font-size: 0.95rem; transition: background 0.2s; }
            .btn-back:hover { background: #7f8c8d; }
        </style>
    </head>
<body>
<br><br><br>
    <div class="form-card">
        <div class="form-header">
            <a href="../../../dashboard.php" style="color: #3498db; text-decoration: none;">← Înapoi la Dashboard</a>
            <h1> Inregistrare utilizator: </h1>
        </div>
        <?php if (isset($mesaj_confirmare)): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: left; font-size: 0.9rem;">
             <?php echo $mesaj_confirmare; ?>
        </div>
        <?php endif; ?>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nume">Nume</label>
                        <input type="text" id="nume" name="nume" required>
                    </div>
                    <div class="form-group">
                        <label for="prenume">Prenume</label>
                        <input type="text" id="prenume" name="prenume" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Nume Utilizator</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="data_nasterii">Data Nașterii </label>
                        <input type="date" name="data_nasterii" placeholder="Data Nasterii" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Rol Sistem</label>
                        <select id="optiuni" name="optiuni" required>
                            <option value="member">Membru </option>
                            <option value="trainer">Antrenor Fitness</option>
                            <option value="kineto">Kinetoterapeut</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Adresă Email</label>
                        <input type="email" id="email" name="email" required placeholder="ceva@gmail.com">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresa">Adresă </label>
                        <input type="text" name="adresa" placeholder="Adresa" required>
                    </div>
                    <div class="form-group">
                        <label for="judet">Județ</label>
                        <input type="text" name="judet" placeholder="Judet" required>
                    </div>
                    <div class="form-group">
                        <label for="oras">Oraș</label>
                        <input type="text" name="oras" placeholder="Oras" required>
                    </div>
                    <div class="form-group">
                        <label for="telefon">Număr de Telefon</label>
                        <input type="text" name="telefon" placeholder="Telefon" required>
                    </div>
                </div>
                <button type="submit">Înregistrează Membru</button>
            </form>
    </div>
</body>
</html>