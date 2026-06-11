<?php
require_once __DIR__ . '/_bootstrap.php';

$data = read_json_body();
$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    json_response(['success' => false, 'message' => 'Emailul și parola sunt obligatorii.'], 400);
}

$stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    json_response(['success' => false, 'message' => 'Email sau parolă incorectă.'], 401);
}

// Păstrăm comportamentul existent din Auth.php: dacă parola nu era setată,
// prima parolă introdusă devine parola contului.
if (empty($user['password_hash'])) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->execute([$hash, $user['id']]);
} elseif (!password_verify($password, $user['password_hash'])) {
    json_response(['success' => false, 'message' => 'Email sau parolă incorectă.'], 401);
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

$current = get_current_user_public($db);
json_response(['success' => true, 'user' => $current]);
