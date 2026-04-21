<?php
// init.php
session_start();
require_once __DIR__ . '/src/Database.php';

// Inițializăm conexiunea o singură dată
$db = Database::getInstance()->getConnection();

