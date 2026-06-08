# SmartKineto 🏋️‍♂️🩺

**Sistem Integrat de Management pentru Clinici de Recuperare și Fitness**

SmartKineto este o aplicație web completă dedicată eficientizării activității din clinicile moderne. Platforma digitalizează procesul de programare, elimină suprapunerile și facilitează o comunicare directă, prin email, între antrenori/terapeuți și pacienți.

---

## 🚀 Funcționalități Principale

* **Sistem de Programări Inteligent :** Validare contextuală care ascunde dinamic orele expirate și previne programările invalide, în real-time.
* **Notificări Persistente :** Sistem de alerte pentru utilizatori, controlat direct din baza de date pentru a garanta salvarea acțiunilor indiferent de sesiune.
* **Comunicare Automatizată :** Trimitere de șabloane HTML pe email pentru programări, cu posibilitatea adăugării de mesaje personalizate de către staff.
* **Gestiune pe Roluri :** Acces stratificat pentru Administrator, Staff (Terapeut/Antrenor) și Membru (Pacient).
* **Export de Date :** Generare rapoarte în format CSV și XML direct din panoul de administrare.

## 🛠️ Tehnologii Utilizate

* **Backend:** PHP  (cu arhitectură bazată pe PDO și Tranzacții SQL)
* **Bază de date:** MySQL
* **Frontend:** HTML, CSS, JavaScript
* **Librării Externe:** PHPMailer

## ⚙️ Instalare și Configurare (Local)

1. Clonează acest repository în folderul de server local (ex: `htdocs` pentru XAMPP).
2. Importă fișierul bazei de date `kim_manager.sql` în phpMyAdmin.
3. Configurează conexiunea la baza de date în fișierul de configurare (ex: `init.php`).
4. Configurează credențialele pentru ca alertele pe email să funcționeze, adăugând fișierul `config_mail.php` cu textul:
`<?php
define('MAIL_USER', 'email-ul vostru');
define('MAIL_PASS', 'parola')`
5. Deschide aplicația în browser: `http://localhost/ProiectWeb/login.php`

## 📄 Licență și Drepturi de Autor

Acest proiect a fost dezvoltat în scop academic și este oferit sub incidența unei licențe libere.
Conținutul pus la dispoziție respectă termenii stipulați de **[Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/)**.
Permite copierea, distribuirea și adaptarea materialului, cu condiția atribuirii corespunzătoare a autorului original.

[//]: # (---)

[//]: # (*Proiect realizat pentru susținerea evaluării de laborator.*)
