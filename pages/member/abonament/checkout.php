<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$user_id = $_SESSION['user_id'];

$prices = [
        'membru' => 250,
        'premium' => 500,
        'vip' => 1000
];

$stmt = $db->prepare("SELECT tier, expires_at FROM subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

$new_tier = isset($_GET['tier']) ? $_GET['tier'] : 'membru';
$fitness = isset($_GET['fitness']) ? 1 : 0;
$forta = isset($_GET['forta']) ? 1 : 0;
$kineto = isset($_GET['kineto']) ? 1 : 0;
$vip_perks = ($new_tier === 'vip') ? 1 : 0;

$today = date('Y-m-d');
$is_upgrade = ($current && $today <= $current['expires_at']);

if ($is_upgrade) {
    $price_new = $prices[$new_tier];
    $price_old = $prices[$current['tier']];
    $total_pay = max(0, $price_new - $price_old);
    $tip_plata = "Upgrade";
} else {
    $total_pay = $prices[$new_tier];
    $tip_plata = "Plată integrală";
}

if (isset($_POST['confirm_payment'])) {
    $ins = $db->prepare("INSERT INTO pending_upgrades 
        (user_id, new_tier, has_fitness, has_forta, has_kineto, has_vip_perks, amount_to_pay) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$user_id, $new_tier, $fitness, $forta, $kineto, $vip_perks, $total_pay]);

    header("Location: abonament.php?pending=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Finalizare Plată - SmartKineto</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            display: flex;
            justify-content: center;
            padding: 50px;
        }

        .checkout-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .price-tag {
            font-size: 2em;
            color: #2ecc71;
            text-align: center;
            margin: 20px 0;
        }

        .details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-confirm {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="checkout-box">
    <h2>Sumar Plată</h2>
    <p>Tip tranzacție: <strong><?php echo $tip_plata; ?></strong></p>

    <div class="details">
        <p>Abonament ales: <strong><?php echo strtoupper($new_tier); ?><?php
                if ($new_tier === 'membru') {
                    if ($fitness) {
                        echo " - Fitness";
                    } elseif ($forta) {
                        echo " - Forta";
                    } elseif ($kineto) {
                        echo " - Kineto";
                    }
                } elseif ($new_tier === 'premium') {
                    if ($fitness && $forta) {
                        echo " - Tip 1";
                    } elseif ($fitness && $kineto) {
                        echo " - Tip 2";
                    } elseif ($forta && $kineto) {
                        echo " - Tip 3";
                    }
                }
                ?></strong></p>
        <p>Valabilitate: 30 zile (după confirmare)</p>
    </div>

    <div class="price-tag">
        <?php echo $total_pay; ?> RON
    </div>

    <p style="font-size: 0.8em; color: #7f8c8d;">
        * Cererea va fi trimisă către trainer pentru validare.
        După ce plata este confirmată, abonamentul va deveni activ.
    </p>

    <form method="POST">
        <button type="submit" name="confirm_payment" class="btn-confirm">Trimite spre validare</button>
    </form>
    <br>
    <a href="choose_plan.php"
       style="display:block; text-align:center; color: #7f8c8d; text-decoration:none;">Anulează</a>
</div>

</body>
</html>