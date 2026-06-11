<?php
require_once __DIR__ . '/_bootstrap.php';

$data = read_json_body();
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = (string)($data['password'] ?? '');
$role = $data['role'] ?? 'member';

$allowedRoles = ['admin', 'trainer', 'member', 'kineto'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'member';
}

if ($name === '' || $email === '' || $password === '') {
    json_response(['success' => false, 'message' => 'Numele, emailul și parola sunt obligatorii.'], 400);
}

$parts = preg_split('/\s+/', $name, 2);
$nume = $parts[0] ?? $name;
$prenume = $parts[1] ?? '';
$username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $name)));
if ($username === '') $username = 'user_' . time();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $db->rollBack();
        json_response(['success' => false, 'message' => 'Email deja înregistrat.'], 409);
    }

    $stmtUser = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmtUser->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    $userId = (int)$db->lastInsertId();

    $stmtDetails = $db->prepare("INSERT INTO user_details (user_id, nume, prenume, telefon) VALUES (?, ?, ?, ?)");
    $stmtDetails->execute([$userId, $nume, $prenume, $phone]);

    $db->commit();
    json_response(['success' => true, 'message' => 'Cont creat.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    json_response(['success' => false, 'message' => 'Eroare la creare cont: ' . $e->getMessage()], 500);
}
