<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

$data = read_json_body();
$userId = (int)$_SESSION['user_id'];
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = (string)($data['password'] ?? '');

if ($name === '' || $email === '') {
    json_response(['success' => false, 'message' => 'Numele și emailul sunt obligatorii.'], 400);
}

$parts = preg_split('/\s+/', $name, 2);
$nume = $parts[0] ?? $name;
$prenume = $parts[1] ?? '';

try {
    $db->beginTransaction();

    if ($password !== '') {
        $stmt = $db->prepare("UPDATE users SET email = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $userId]);
    } else {
        $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $userId]);
    }

    $stmt = $db->prepare("SELECT id FROM user_details WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE user_details SET nume = ?, prenume = ?, telefon = ? WHERE user_id = ?");
        $stmt->execute([$nume, $prenume, $phone, $userId]);
    } else {
        $stmt = $db->prepare("INSERT INTO user_details (user_id, nume, prenume, telefon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $nume, $prenume, $phone]);
    }

    $db->commit();
    $user = get_current_user_public($db);
    json_response(['success' => true, 'user' => $user]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    json_response(['success' => false, 'message' => 'Eroare profil: ' . $e->getMessage()], 500);
}
