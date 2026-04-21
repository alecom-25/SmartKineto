<?php
require_once __DIR__ . '/init.php';

$_SESSION = array();

session_destroy();

header("Location: login.php");
exit();

