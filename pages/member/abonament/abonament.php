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

$stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sub) {
    // Dacă nu are abonament, îl trimitem la pagina de planuri cu un mesaj
    header("Location: choose_plan.php");
    exit();
}

$today = date('Y-m-d');

$is_expired = ($today > $sub['expires_at']);

if ($is_expired) {
    $status_text = "EXPIRAT";
    $status_class = "status-expired";
} else {
    $status_text = "ACTIV";
    $status_class = "status-active";
}

if (isset($_GET['hide_rejected'])) {
    $req_id = $_GET['hide_rejected'];
    // Salvăm în sesiune că acest ID a fost închis
    $_SESSION['hidden_notifications'][] = $req_id;

    header("Location: abonament.php");
    exit();
}

$stmt_rejected = $db->prepare("SELECT * FROM pending_upgrades WHERE user_id = ? AND status = 'rejected' 
                                                    ORDER BY created_at DESC LIMIT 1");
$stmt_rejected->execute([$user_id]);
$rejected_request = $stmt_rejected->fetch(PDO::FETCH_ASSOC);

$show_notification = false;
if ($rejected_request) {
    // verificăm daca ID-ul cererii nu este în lista de notificari ascunse din sesiune
    if (!isset($_SESSION['hidden_notifications']) || !in_array($rejected_request['id'], $_SESSION['hidden_notifications'])) {
        $show_notification = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Abonamentul Meu - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f6;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .sub-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 10px solid #3498db;
        }
        .tier-name {
            font-size: 2.5em;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
        }
        .status-active {
            color: #2ecc71;
            font-weight: bold;
        }
        .benefits-list {
            margin-top: 20px;
            list-style: none;
            padding: 0;
        }
        .benefits-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .benefits-list li::before {
            content: "✓";
            color: #2ecc71;
            margin-right: 10px;
            font-weight: bold;
        }

        .promo-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 5px solid #1976d2;
        }
        .btn-action {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .vip-gold {
            border-top-color: #f1c40f;
        }
        .sub-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-top: 25px;
        }
        .sub-actions form{
            margin: 0;
            padding: 0;
            /*display: flex;*/
        }
        .btn-action, .btn-suspend {
            height: 45px;
            min-width: 160px;

            appearance: none;
            -webkit-appearance: none;
            border: none;
            outline: none;
            margin: 0;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            padding: 0 20px;
            font-family: inherit;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;

            /*text-align: center;*/
            /*line-height: 1.5;*/
        }
        .btn-action {
            background-color: #3498db;
            color: white !important;
        }

        .btn-suspend {
            background-color: <?php echo $sub['is_suspended'] ? '#2ecc71' : '#e67e22'; ?>;
            color: white;
        }

        .btn-action:hover, .btn-suspend:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../../../dashboard.php" style="text-decoration: none; color: #7f8c8d;">← Dashboard</a>

<!--    --><?php //if ($rejected_request): ?>
<!--        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb; margin-bottom: 20px; position: relative;">-->
<!--            <strong>Ne pare rău!</strong> Ultima ta cerere de upgrade la planul-->
<!--            <strong>--><?php //echo strtoupper($rejected_request['new_tier']); ?><!--</strong>-->
<!--            a fost respinsă de către trainer.-->
<!--            <br><small>Te rugăm să contactezi trainerul la recepție pentru detalii privind plata.</small>-->
<!---->
<!--        </div>-->
<!--    --><?php //endif; ?>

    <?php if ($show_notification): ?>
        <div class="alert-rejected" style="background: #f8d7da; padding: 15px; position: relative;">
            <a href="?hide_rejected=<?php echo $rejected_request['id']; ?>" style="position: absolute; right: 10px; top: 5px; text-decoration: none;">&times;</a>

            <strong>Upgrade Respins:</strong> Cererea pentru <?php echo strtoupper($rejected_request['new_tier']); ?> nu a fost aprobată.
        </div>
    <?php endif; ?>

    <?php if ($sub): ?>
    <div class="sub-card <?php echo $sub['tier'] == 'vip' ? 'vip-gold' : ''; ?>">
        <p style="margin: 0; color: #7f8c8d;">Status Abonament:
            <?php if ($today <= $sub['expires_at']): ?>
                <span style="color: #2ecc71; font-weight: bold;">ACTIV</span>
            <?php else: ?>
                <span style="color: #e74c3c; font-weight: bold;">EXPIRAT</span>
            <?php endif; ?>
            <?php if ($sub['is_suspended']): ?>
                <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Abonament Suspendat:</strong> Beneficiile tale sunt momentan înghețate.
                </div>
        <?php endif; ?>
        </p>

        <h1 class="tier-name"><?php echo strtoupper($sub['tier']); ?></h1>

        <?php if ($today > $sub['expires_at']): ?>
            <p style="color: #e74c3c; font-weight: bold;">⚠️ Atenție! Abonamentul a expirat pe <?php echo date('d.m.Y', strtotime($sub['expires_at'])); ?></p>
            <p>Reînnoiește acum pentru a păstra accesul la facilități.</p>
        <?php else: ?>
            <p>Valabil până la: <strong><?php echo date('d.m.Y', strtotime($sub['expires_at'])); ?></strong></p>
        <?php endif; ?>

        <?php if ($today <= $sub['expires_at']): ?>
        <ul class="benefits-list">
                <?php if ($sub['has_fitness']): ?> <li>Acces zona Fitness (Cardio & Nutriție)</li> <?php endif; ?>
                <?php if ($sub['has_forta']): ?> <li>Acces zona Forță (Greutăți)</li> <?php endif; ?>
                <?php if ($sub['has_kineto']): ?> <li>Pachet Kinetoterapie (Evaluare & Masaj Terapeutic)</li> <?php endif; ?>
                <?php if ($sub['has_vip_perks']): ?>
                    <li>Masaj săptămânal inclus</li>
                    <li>Parcare gratuită</li>
                    <li>Prioritate programări</li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

            <div class="sub-actions">
                <a href="choose_plan.php" class="btn-action">Schimbă sau Prelungește</a>

                <form action="suspend.php" method="POST" onsubmit="return confirm('Sigur dorești să modifici starea abonamentului?');">
                <button type="submit" class="btn-suspend">
                    <?php echo $sub['is_suspended'] ? 'Reactivează Abonament' : 'Intrerupere Abonament'; ?>
                </button>
                </form>
            </div>
        </div>

        <?php if ($sub['tier'] != 'vip'): ?>
            <div class="promo-box">
                <h3>Oportunități de Upgrade ✨</h3>
                <?php if ($sub['tier'] == 'membru'): ?>
                    <p>Treci la <strong>PREMIUM</strong> pentru a combina două zone (ex: Fitness + Kineto) și economisește 15% față de plata separată!</p>
                <?php elseif ($sub['tier'] == 'premium'): ?>
                    <p>Devino membru <strong>VIP</strong> pentru acces total și beneficii exclusive precum parcare gratuită și prioritate la programări!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="sub-card">
            <h1>Nu ai un abonament activ</h1>
            <p>Se pare că nu ai ales încă un plan de antrenament sau recuperare.</p>
            <a href="choose_plan.php" class="btn-action">Vezi Oferta de Abonamente</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>