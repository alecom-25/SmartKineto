<?php
// init.php
session_start();
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance()->getConnection();
