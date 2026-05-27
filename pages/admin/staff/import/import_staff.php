<?php
require_once __DIR__ . '/../../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_upload'])) {
    $file = $_FILES['file_upload'];

    // verificam daca s-a incarcat ok
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['admin_msg'] = "Eroare la încărcarea fișierului pe server";
        header("Location: manage_staff.php");
        exit();
    }

    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $tmp_path = $file['tmp_name'];

    $imported_count = 0;
    $skipped_count = 0;

    try {
        // fisier csv
        if ($ext === 'csv') {
            if (($handle = fopen($tmp_path, "r")) !== FALSE) {

                // ignoram numele coloanelor
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle); // daca nu are, luam de la inceput
                }

                // Citim prima linie (capul de tabel) pentru a o sări
                fgetcsv($handle, 1000, ",");

                // Parcurgem rând cu rând fișierul CSV
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Structura CSV: [0]=>ID, [1]=>Nume, [2]=>Prenume, [3]=>Email, [4]=>Rol
                    if (count($data) < 5) continue; // Sărim peste rândurile incomplete

                    $nume = trim($data[1]);
                    $prenume = trim($data[2]);
                    $email = trim($data[3]);
                    $rol = strtolower(trim($data[4]));

                    $role = (strpos($rol, 'kinetoterapeut') !== false) ? 'kineto' : 'trainer';

                    if (empty($email) || empty($nume)) {
                        continue;
                    }

                    // verificam daca email-ul exista deja in db
                    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $check->execute([$email]);

                    if ($check->rowCount() == 0) {
                        // daca nu, il inseram in users
                        $stmtUser = $db->prepare("INSERT INTO users (email, role) VALUES (?, ?)");
                        $stmtUser->execute([$email, $role]);
                        $new_user_id = $db->lastInsertId();

                        // si un user_details
                        $stmtDetails = $db->prepare("INSERT INTO user_details (user_id, nume, prenume) VALUES (?, ?, ?)");
                        $stmtDetails->execute([$new_user_id, $nume, $prenume]);

                        $imported_count++;
                    } else {
                        $skipped_count++;
                    }
                }
                fclose($handle);
            }
        }
        // fisier xml
        elseif ($ext === 'xml') {
            // incarcam fisierul xml
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($tmp_path);

            if ($xml === false) {
                $_SESSION['admin_msg'] = "Fișierul XML nu are o structură validă.";
                header("Location: manage_staff.php");
                exit();
            }

            // Parcurgem fiecare nod din fisier
            foreach ($xml->angajat as $angajat) {
                $nume = trim((string)$angajat->nume);
                $prenume = trim((string)$angajat->prenume);
                $email = trim((string)$angajat->email);
                $rol = strtolower(trim((string)$angajat->rol));

                $role = (strpos($rol, 'kinetoterapeut') !== false) ? 'kineto' : 'trainer';

                if (empty($email) || empty($nume)){
                    continue;
                }

                // verificam daca exista deja in db
                $check = $db->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);

                if ($check->rowCount() == 0) {
                    // daca nu, il inseram in users
                    $stmtUser = $db->prepare("INSERT INTO users (email, role) VALUES (?, ?)");
                    $stmtUser->execute([$email, $role]);
                    $new_user_id = $db->lastInsertId();

                    // si in user_details
                    $stmtDetails = $db->prepare("INSERT INTO user_details (user_id, nume, prenume) VALUES (?, ?, ?)");
                    $stmtDetails->execute([$new_user_id, $nume, $prenume]);

                    $imported_count++;
                } else {
                    $skipped_count++;
                }
            }
        } else {
            $_SESSION['admin_msg'] = "Format neacceptat! Încărcați doar fișiere .csv sau .xml";
            header("Location: manage_staff.php");
            exit();
        }

        $msg = "✅ Import finalizat! Au fost adăugați $imported_count angajați noi.";
        if ($skipped_count > 0) {
            $msg .= " ($skipped_count conturi au fost sărite deoarece email-ul exista deja)";
        }

        $_SESSION['admin_msg'] = $msg;

    } catch (Exception $e) {
        $_SESSION['admin_msg'] = "Eroare la procesarea bazei de date: " . $e->getMessage();
    }
}

header("Location: ../manage_staff.php");
exit();