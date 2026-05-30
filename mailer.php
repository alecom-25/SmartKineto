<?php
// Importăm clasele PHPMailer în spațiul de nume curent
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/config_mail.php';

function trimiteEmail($catre_email, $catre_nume, $subiect, $mesaj_html) {
    $mail = new PHPMailer(true);

    try {
        // Setări Server SMTP (Google)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Setări Expeditor și Destinatar
        // Înlocuiește tot cu gmail-ul tău și numele sălii
        $mail->setFrom('totosicarmelia@gmail.com', 'SmartKineto Admin');
        $mail->addAddress($catre_email, $catre_nume);

        // Conținutul Mailului
        $mail->isHTML(true); // Îi spunem că trimitem un mail cu design HTML
        $mail->Subject = $subiect;
        $mail->Body    = $mesaj_html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Dacă apare o eroare, o salvăm în logurile serverului ca să știm de ce nu a mers
        error_log("Eroare la trimitere email: {$mail->ErrorInfo}");
        return false;
    }
}

function trimiteMailConfirmare($email, $nume, $data, $ora, $tip, $sala, $mesaj_personalizat) {
    $subiect = "Confirmare Rezervare - SmartKineto";
    $mesaj = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #2c3e50;'>Salutare, " . htmlspecialchars($nume) . "!</h2>
            <p>Te-ai programat cu succes la o nouă ședință.</p>
            <p>📅 Data: <strong>{$data}</strong></p>
            <p>🕒 Ora: <strong>{$ora}</strong></p>
            <p>🏋️‍♂️ Tip: <strong>" . ucfirst($tip) . "</strong></p>
            <p>📍 Sală: <strong>" . ucfirst($sala) . "</strong></p>
    ";

    if (!empty($mesaj_personalizat)) {
        $mesaj .= "
            <div style='background-color: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; color: #8a6d3b; font-size: 14px;'>
                    <strong>🗨️ Mesaj de la antrenorul tău:</strong><br>
                    " . nl2br(htmlspecialchars($mesaj_personalizat)) . "
                </p>
            </div>
        ";
    }

    $mesaj .="
            <hr>
            <p style='color: #e74c3c;'>⚠️ Nu întârzia mai mult de 15 minute, altfel ședința se anulează!</p>
        </div>
    ";
    // Apelăm motorul
    return trimiteEmail($email, $nume, $subiect, $mesaj);
}

function trimiteMailReprogramare($email, $nume, $old_data, $old_ora, $new_data, $new_ora, $tip, $sala, $mesaj_personalizat) {
    $subiect = "Reprogramare Rezervare - SmartKineto";
    $mesaj = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #2c3e50;'>Salutare, " . htmlspecialchars($nume) . "!</h2>
            <p>Ședinta ta a fost reprogramata.</p>
            <p>📅 Vechea Data: <strong>{$old_data}</strong></p>
            <p>🕒 Vechea Ora: <strong>{$old_ora}</strong></p>
            <p>📅 Noua Data: <strong>{$new_data}</strong></p>
            <p>🕒 Noua Ora: <strong>{$new_ora}</strong></p>
            <p>🏋️‍ Tip: <strong>" . ucfirst($tip) . "</strong></p>
            <p>📍 Sală: <strong>" . ucfirst($sala) . "</strong></p>
    ";

    if (!empty($mesaj_personalizat)) {
        $mesaj .= "
            <div style='background-color: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; color: #8a6d3b; font-size: 14px;'>
                    <strong>🗨️ Mesaj de la antrenorul tău:</strong><br>
                    " . nl2br(htmlspecialchars($mesaj_personalizat)) . "
                </p>
            </div>
        ";
    }

    $mesaj .= "
        <hr>
            <p style='color: #e74c3c;'>⚠️ Nu întârzia mai mult de 15 minute, altfel ședința se anulează!</p>
            <p> Pentru orice nelamuriri vă puteți contacta antrenorul sau adminul folosind numerele de telefon afișate pe site </p>
        </div>
    ";
    // Apelăm motorul
    return trimiteEmail($email, $nume, $subiect, $mesaj);
}

function trimiteMailAnulare($email, $nume, $data, $tip, $mesaj_personalizat) {
    $subiect = " Anulare Rezervare - SmartKineto";
    $mesaj = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #2c3e50;'>Salutare, " . htmlspecialchars($nume) . "!</h2>
            <p>Cu părere de rău te anunțăm că ședința ta a fost anulată</p>
            <p>Datele ședinței: </p>
            <p>📅 Data: <strong>{$data}</strong></p>
            <p>📍 Tip: <strong>" . ucfirst($tip) . "</strong></p>
    ";

    if (!empty($mesaj_personalizat)) {
        $mesaj .= "
            <div style='background-color: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; color: #8a6d3b; font-size: 14px;'>
                    <strong>🗨️ Mesaj de la antrenorul tău:</strong><br>
                    " . nl2br(htmlspecialchars($mesaj_personalizat)) . "
                </p>
            </div>
        ";
    }

    $mesaj .= "
            <hr>
            <p> Pentru orice nelamuriri vă puteți contacta antrenorul sau adminul folosind numerele de telefon afișate pe site </p>
        </div>
    ";
    // Apelăm motorul
    return trimiteEmail($email, $nume, $subiect, $mesaj);
}