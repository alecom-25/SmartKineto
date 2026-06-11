<?php
require_once __DIR__ . '/_bootstrap.php';
require_login();

// Pentru graficul din frontend trimitem aceeași listă de sesiuni ca în data.php,
// dar fără să duplicăm toată logica: includem data.php ar produce direct output,
// așa că aici folosim un răspuns simplu care lasă frontendul să folosească Store-ul curent.
json_response(['success' => true, 'sessions' => null]);
