<?php
// src/Auth.php

class Auth
{
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            // 3. Dacă totul e OK, salvăm datele importante în SESIUNE
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            return true;
        }

        return false;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}