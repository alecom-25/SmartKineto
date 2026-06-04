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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #3075d1 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .logo-area {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: 600;
        }

        p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #dcdde1;
            border-radius: 8px;
            font-size: 1rem;
            color: #2c3e50;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .input-group input:focus {
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Alertă eroare drăguță */
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Autentificare</h2>

    <?php if ($eroare): ?>
        <div class="error-alert"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="input-group">
            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>
        </div>
        <div class="input-group">
            <label>Parolă:</label><br>
            <input type="password" name="password" required><br><br>
        </div>

        <button type="submit" class="login-btn">Intră în cont</button>

    </form>
</div>
</body>
</html>