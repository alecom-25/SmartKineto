<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

$user = get_current_user_public($db);
if (!$user) {
    json_response(['success' => false, 'message' => 'Utilizatorul nu mai există.'], 404);
}

json_response(['success' => true, 'user' => $user]);
