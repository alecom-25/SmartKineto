<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT is_suspended FROM subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

if ($current) {
    $new_status = $current['is_suspended'] ? 0 : 1;

    $update = $db->prepare("UPDATE subscriptions SET is_suspended = ? WHERE user_id = ?");
    $update->execute([$new_status, $user_id]);

    $msg = $new_status ? "Suspendare abonament" : "Reactivare abonament";
    $hist = $db->prepare("INSERT INTO activities_history (user_id, activity_type, description) VALUES (?, 'system', ?)");
    $hist->execute([$user_id, $msg]);
}

header("Location: abonament.php");
exit();
