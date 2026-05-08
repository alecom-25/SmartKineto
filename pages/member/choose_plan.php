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
    $params = http_build_query($_POST);
    header("Location: checkout.php?" . $params);
    exit();
//    Pentru trainer la validarea platii!!!!!!!!!!!!!!!!!!!!!
//    $user_id = $_SESSION['user_id'];
//    $tier = $_POST['tier'];
//
//    $fitness = isset($_POST['fitness']) ? 1 : 0;
//    $forta = isset($_POST['forta']) ? 1 : 0;
//    $kineto = isset($_POST['kineto']) ? 1 : 0;
//    $vip_perks = ($tier === 'vip') ? 1 : 0;
//
//    $descriere = "";
//    $suma = 0;
//
//    if($tier === 'membru'){
//        $suma = 250;
//        if($fitness){
//            $descriere = "Abonament - Fitness";
//        }elseif ($forta){
//            $descriere = "Abonament - Forta";
//        }elseif ($kineto){
//            $descriere = "Abonament - Kineto";
//        }
//    }elseif($tier === 'premium'){
//        if($fitness && $forta){
//            $descriere = "Abonament - Tip 1";
//            $suma = 550;
//        }elseif ($fitness && $kineto){
//            $descriere = "Abonament - Tip 2";
//            $suma = 600;
//        }elseif($forta && $kineto){
//            $descriere = "Abonament - Tip 3";
//            $suma = 650;
//        }
//    }elseif($tier === 'vip'){
//        $descriere = "Abonament - VIP";
//        $suma = 1000;
//    }
//
//    $start_date = date('Y-m-d');
//    $expires_at = date('Y-m-d', strtotime('+1 month'));
//
//    $check = $db->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
//    $check->execute([$user_id]);
//
//    if ($check->fetch()) {
//        $sql = "UPDATE subscriptions SET tier = ?, has_fitness = ?, has_forta = ?, has_kineto = ?, has_vip_perks = ?, start_date = ?, expires_at = ? WHERE user_id = ?";
//        $stmt = $db->prepare($sql);
//        $stmt->execute([$tier, $fitness, $forta, $kineto, $vip_perks, $start_date, $expires_at, $user_id]);
//    } else {
//        $sql = "INSERT INTO subscriptions (user_id, tier, has_fitness, has_forta, has_kineto, has_vip_perks, start_date, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
//        $stmt = $db->prepare($sql);
//        $stmt->execute([$user_id, $tier, $fitness, $forta, $kineto, $vip_perks, $start_date, $expires_at]);
//    }
//
//    $sql1 = "INSERT INTO activities_history (user_id, activity_type, description, amount) VALUES (?, ?, ?, ?)";
//    $stmt1 = $db->prepare($sql1);
//    $stmt1->execute([$user_id, "payment", $descriere, $suma]);
//
//    header("Location: abonament.php");
//    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Alege Planul - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .section-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            width: 300px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .card h3 {
            margin-top: 0;
            color: #3498db;
        }
        .price {
            font-size: 1.5em;
            font-weight: bold;
            margin: 15px 0;
        }
        .btn-buy {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        .btn-buy:hover {
            background: #2980b9;
        }
        .tier-tag {
            font-size: 0.7em;
            background: #eee;
            padding: 3px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .vip-card {
            border: 2px solid #f1c40f;
            background: #fffdf0;
        }
    </style>
</head>
<body>

<h1 class="section-title">Alege noul tău abonament</h1>

<div class="grid">
    <div class="card">
        <span class="tier-tag">Membru</span>
        <h3>Fitness</h3>
        <p class="price">250 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="membru">
            <input type="hidden" name="fitness" value="1">
            <button type="submit" class="btn-buy">Alege Fitness</button>
        </form>
    </div>

    <div class="card">
        <span class="tier-tag">Membru</span>
        <h3>Forță</h3>
        <p class="price">250 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="membru">
            <input type="hidden" name="forta" value="1">
            <button type="submit" class="btn-buy">Alege Forță</button>
        </form>
    </div>

    <div class="card">
        <span class="tier-tag">Membru</span>
        <h3>Kinetoterapie</h3>
        <p class="price">250 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="membru">
            <input type="hidden" name="kineto" value="1">
            <button type="submit" class="btn-buy">Alege Kineto</button>
        </form>
    </div>

    <div class="card">
        <span class="tier-tag">Premium</span>
        <h3>Tip 1: Fit + Forță</h3>
        <p class="price">500 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="premium">
            <input type="hidden" name="fitness" value="1">
            <input type="hidden" name="forta" value="1">
            <button type="submit" class="btn-buy">Alege Premium 1</button>
        </form>
    </div>

    <div class="card">
        <span class="tier-tag">Premium</span>
        <h3>Tip 2: Fit + Kineto</h3>
        <p class="price">500 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="premium">
            <input type="hidden" name="fitness" value="1">
            <input type="hidden" name="kineto" value="1">
            <button type="submit" class="btn-buy">Alege Premium 2</button>
        </form>
    </div>

    <div class="card">
        <span class="tier-tag">Premium</span>
        <h3>Tip 3: Forță + Kineto</h3>
        <p class="price">500 RON</p>
        <form method="POST">
            <input type="hidden" name="tier" value="premium">
            <input type="hidden" name="forta" value="1">
            <input type="hidden" name="kineto" value="1">
            <button type="submit" class="btn-buy">Alege Premium 3</button>
        </form>
    </div>

    <div class="card vip-card">
        <span class="tier-tag" style="background:#f1c40f">All Inclusive</span>
        <h3>VIP</h3>
        <p class="price">1000 RON</p>
        <ul style="text-align:left; font-size: 0.85em;">
            <li>Fitness + Forță + Kineto</li>
            <li>Masaj săptămânal</li>
            <li>Parcare gratuită</li>
        </ul>
        <form method="POST">
            <input type="hidden" name="tier" value="vip">
            <input type="hidden" name="fitness" value="1">
            <input type="hidden" name="forta" value="1">
            <input type="hidden" name="kineto" value="1">
            <button type="submit" class="btn-buy" style="background:#f1c40f; color:#000">Go VIP</button>
        </form>
    </div>
</div>

<p style="text-align:center; margin-top:30px;">
    <a href="abonament.php">Înapoi fără modificări</a>
</p>

</body>
</html>