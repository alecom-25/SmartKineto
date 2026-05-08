<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'trainer') {
//     die("Acces interzis");
    header("Location: ../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if (isset($_POST['approve_id'])) {
    $request_id = $_POST['approve_id'];

    $stmt = $db->prepare("SELECT * FROM pending_upgrades WHERE id = ? and status = ?");
    $stmt->execute([$request_id, 'pending']);
    $req = $stmt->fetch();

    if ($req) {
        $db->beginTransaction();

        try {
            $db->prepare("UPDATE pending_upgrades SET status = 'approved' WHERE id = ?")->execute([$request_id]);

            $expires_at = date('Y-m-d', strtotime('+1 month'));
            $sqlSub = "INSERT INTO subscriptions (user_id, tier, has_fitness, has_forta, has_kineto, has_vip_perks, expires_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE 
                       tier=VALUES(tier), has_fitness=VALUES(has_fitness), 
                       has_forta=VALUES(has_forta), has_kineto=VALUES(has_kineto), 
                       has_vip_perks=VALUES(has_vip_perks), expires_at=VALUES(expires_at), 
                       is_suspended = 0";
            $db->prepare($sqlSub)->execute([
                $req['user_id'], $req['new_tier'], $req['has_fitness'],
                $req['has_forta'], $req['has_kineto'], $req['has_vip_perks'], $expires_at
            ]);

            $abonament = "";
            if($req['new_tier'] === 'membru'){
                if($req['has_fitness']){
                    $abonament =  " - Fitness";
                }elseif ($req['has_forta']){
                    $abonament = " - Forta";
                }elseif ($req['has_kineto']){
                    $abonament = " - Kineto";
                }
            }elseif($req['new_tier'] === 'premium'){
                if($req['has_fitness'] && $req['has_forta']){
                    $abonament = " - Tip 1";
                }elseif ($req['has_fitness'] && $req['has_kineto']){
                    $abonament = " - Tip 2";
                }elseif($req['has_forta'] && $req['has_kineto']){
                    $abonament = " - Tip 3";
                }
            }elseif($req['new_tier'] === 'vip'){
                $abonament = "VIP";
            }

            $desc = "Abonament Confirmat - " . strtoupper($req['new_tier']) . $abonament;
            $db->prepare("INSERT INTO activities_history (user_id, activity_type, description, amount) 
                        VALUES (?, 'payment', ?, ?)")
                ->execute([$req['user_id'], $desc, $req['amount_to_pay']]);

            $db->commit();
            $msg = "Plată validată cu succes!";
        } catch (Exception $e) {
            $db->rollBack();
            $msg = "Eroare: " . $e->getMessage();
        }
    }
}elseif (isset($_POST['reject_id'])) {
    $request_id = $_POST['reject_id'];
    $stmt = $db->prepare("UPDATE pending_upgrades SET status = 'rejected' WHERE id = ? AND status = ?");
    $stmt->execute([$request_id, 'pending']);
    $msg = "Cererea a fost respinsă.";
}

$pending_list = $db->query("SELECT p.*, u.username FROM pending_upgrades p JOIN users u ON p.user_id = u.id 
                                                    WHERE p.status = 'pending'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Validare Plăți - Trainer</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f8f9fa;
        }
        .btn-approve {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-reject {
            background: #cc2e40;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn{
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn-history {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #418ed6;
            color: white !important;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-history:hover {
            background-color: #3576b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-history:active {
            transform: translateY(0);
        }

        .btn-return {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #63c33b;
            color: white !important;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-return:hover {
            background-color: #4e982e;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-return:active {
            transform: translateY(0);
        }

        /*.history-container {*/
        /*    margin-bottom: 20px;*/
        /*    display: flex;*/
        /*    justify-content: flex-end;*/
        /*}*/
    </style>
</head>
<body>
    <h1>Ceri de Upgrade în Așteptare</h1>
    <?php if(isset($msg)) echo "<p>$msg</p>"; ?>

    <div class="btn">
        <a href="../../dashboard.php" class="btn-return">← Înapoi la Dashboard</a>
        <a href="payment_history.php" class="btn-history">Vezi Istoric Toate Cererile</a>
    </div>
    <table>
        <thead>
        <tr>
            <th>Membru</th>
            <th>Plan Nou</th>
            <th>Suma de primit</th>
            <th>Data Cererii</th>
            <th>Acțiune</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($pending_list as $p): ?>
            <tr>
                <td><?php echo $p['username']; ?></td>
                <td><?php echo strtoupper($p['new_tier']); ?></td>
                <td><strong><?php echo $p['amount_to_pay']; ?> RON</strong></td>
                <td><?php echo $p['created_at']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="approve_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="btn-approve">Confirmă Plata</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="reject_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="btn-reject">Refuza Upgrade-ul</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>