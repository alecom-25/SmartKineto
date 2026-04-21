<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/src/Auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$auth = new Auth($db);
$eroare = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($auth->login($email, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $eroare = "Email sau parolă incorectă!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - SmartKineto</title>
</head>
<body>
    <h2>Autentificare</h2>

    <?php if ($eroare): ?>
        <p style="color: red;"><?php echo $eroare; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Parolă:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Intră în cont</button>
    </form>
</body>
</html>