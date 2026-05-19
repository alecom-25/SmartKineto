<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title> Manager_users</title>
        <style>
            .card-btn {
                background: white;
                padding: 30px 20px;
                text-align: center;
                text-decoration: none;
                color: #34495e;
                border-radius: 10px;
                border-left: 5px solid #e74c3c;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: transform 0.2s, box-shadow 0.2s;
                display: flex;
                flex-direction: column;
                align-items: center;
                border: 1px solid #ddd;
            }

            .card-btn:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 15px rgba(0,0,0,0.15);
                background-color: #ebf5fb;
            }
        </style>
    </head>
    <body>
        <br>
        <a href="../../dashboard.php" style="color: #3498db; text-decoration: none;">← Înapoi la Dashboard</a>
        <br><br>
        <a href="register_member.php" class="card-btn">➕ Adaugă Membru Nou</a>
    </body>
</html>