<?php
// 1. Includem conexiunea făcută mai sus
require_once 'config.php';

// 2. Facem o interogare (Query) să vedem ce avem în tabelul de users
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Afișăm datele sub formă de JSON (ca să poată fi citite de JS mai târziu)
    header('Content-Type: application/json');
    echo json_encode($allUsers);

} catch (PDOException $e) {
    echo "Eroare SQL: " . $e->getMessage();
}
?>