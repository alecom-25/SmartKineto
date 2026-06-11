<?php
// Fișier comun pentru toate endpoint-urile API.
// Pornește sesiunea, conectează baza de date și definește funcții ajutătoare.

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json; charset=utf-8');

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return $_POST ?: [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return $_POST ?: [];
    }

    return $data;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Utilizator neautentificat.'], 401);
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        json_response(['success' => false, 'message' => 'Nu ai permisiune pentru această acțiune.'], 403);
    }
}

function public_role(string $role): string
{
    // Frontendul are ecran pentru trainer. Kinetoterapeutul folosește același tip de meniu.
    return $role;
}

function full_name(?string $nume, ?string $prenume, ?string $username): string
{
    $name = trim(($nume ?? '') . ' ' . ($prenume ?? ''));
    return $name !== '' ? $name : (string)($username ?? 'Utilizator');
}

function public_user(array $row): array
{
    $role = public_role($row['role'] ?? 'member');

    return [
        'id' => (string)$row['id'],
        'name' => full_name($row['nume'] ?? null, $row['prenume'] ?? null, $row['username'] ?? null),
        'username' => $row['username'] ?? '',
        'email' => $row['email'] ?? '',
        'phone' => $row['telefon'] ?? '',
        'role' => $role,
        'status' => 'active',
        'joinDate' => isset($row['created_at']) ? substr((string)$row['created_at'], 0, 10) : date('Y-m-d'),
        'specialization' => ($role === 'kineto') ? 'Kinetoterapie' : (($role === 'trainer') ? 'Fitness / antrenor' : ''),
        'schedule' => '',
    ];
}

function get_current_user_public(PDO $db): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $db->prepare("\n        SELECT u.id, u.username, u.email, u.role, u.created_at,\n               ud.nume, ud.prenume, ud.telefon\n        FROM users u\n        LEFT JOIN user_details ud ON ud.user_id = u.id\n        WHERE u.id = ?\n        LIMIT 1\n    ");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? public_user($row) : null;
}

function map_session_type_to_frontend(?string $category): string
{
    if (!$category) {
        return 'mixed';
    }
    if (str_starts_with($category, 'kineto')) {
        return 'kineto';
    }
    if ($category === 'personal_training') {
        return 'strength';
    }
    if ($category === 'fitness_group') {
        return 'fitness';
    }
    return 'mixed';
}

function estimate_subscription_price(array $sub): int
{
    $price = 0;
    if (($sub['tier'] ?? '') === 'premium') $price += 250;
    if (($sub['tier'] ?? '') === 'vip') $price += 500;
    if (!empty($sub['has_fitness'])) $price += 100;
    if (!empty($sub['has_forta'])) $price += 100;
    if (!empty($sub['has_kineto'])) $price += 150;
    return $price > 0 ? $price : 100;
}

function subscription_status(array $sub): string
{
    if (!empty($sub['is_suspended'])) {
        return 'suspended';
    }
    if (!empty($sub['expires_at']) && date('Y-m-d') > $sub['expires_at']) {
        return 'expired';
    }
    return 'active';
}
