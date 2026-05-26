<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kineto') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$therapist_id = $_SESSION['user_id'];

// Preluăm toți pacienții unici care au avut sau au vreo ședință programată cu acest kinetoterapeut
$stmt = $db->prepare(" SELECT DISTINCT u.id, ud.nume, ud.prenume, u.email FROM users u
    JOIN user_details ud ON u.id = ud.user_id JOIN patient_medical_records pmr ON u.id = pmr.patient_id 
    WHERE pmr.therapist_id = ? ORDER BY ud.nume ASC, ud.prenume ASC");
$stmt->execute([$therapist_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title> Pacienții Mei - SmartKineto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        /* layout flexibil - stânga/dreapta */
        .workspace {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .sidebar {
            flex: 1;
            max-width: 350px;
        }

        .main-card {
            flex: 2;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            min-height: 400px;
        }

        /* butoane navigare */
        .nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-back {
            background: #e9ecef;
            color: #495057;
        }

        .btn-add {
            background: #2ecc71;
            color: white;
        }

        .btn-save {
            background: #3498db;
            color: white;
            width: 100%;
            margin-top: 15px;
            padding: 12px;
        }

        /* butoanele clienților din stanga */
        .client-btn {
            display: block;
            width: 100%;
            text-align: left;
            background: white;
            border: 1px solid #e2e8f0;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .client-btn:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        .client-btn.active {
            background: #3498db;
            color: white;
            border-color: #2980b9;
        }

        /* Formular și Liste */
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #4a5568;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
            resize: vertical;
        }

        .sessions-list {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #94a3b8;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            float: right;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-header">
        <a href="../../../dashboard.php" class="btn btn-back">← Dashboard</a>
        <a href="assign_pacient.php" class="btn btn-add">➕ Adaugă Pacient Nou</a>
    </div>

    <h1>📋 Management Pacienți - Kinetoterapie</h1>

    <?php if (isset($_SESSION['client_msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
            <?php echo $_SESSION['client_msg'];
            unset($_SESSION['client_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="workspace">
        <div class="sidebar">
            <h3>Lista Pacienți</h3>
            <?php if (empty($clients)): ?>
                <p style="color:#777; font-style:italic;">Nu ai niciun pacient înregistrat în istoric.</p>
            <?php else: ?>
                <?php foreach ($clients as $c): ?>
                    <button class="client-btn" id="btn-client-<?php echo $c['id']; ?>"
                            onclick="loadPatientDetails(<?php echo $c['id']; ?>, '<?php echo $c['nume'] . ' ' . $c['prenume']; ?>')">
                        👤 <?php echo $c['nume'] . ' ' . $c['prenume']; ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="main-card" id="medical_file_container">
            <div style="text-align: center; color: #94a3b8; margin-top: 100px;">
                <span style="font-size: 50px;">📂</span>
                <h3>Selectează un pacient din lista din stânga pentru a-i deschide fișa medicală.</h3>
            </div>
        </div>
    </div>
</div>

<script>
    function loadPatientDetails(patientId, patientName) {
        // Schimbăm clasa activă pe butoane
        document.querySelectorAll('.client-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('btn-client-' + patientId).classList.add('active');

        const card = document.getElementById('medical_file_container');
        card.innerHTML = '<h3>🔄 Se încarcă datele clinice...</h3>';

        // apelam AJAX
        fetch(`get_client_details.php?patient_id=${patientId}`)
            .then(res => res.json())
            .then(data => {
                // Generăm structura HTML dinamic direct în interiorul cardului medical
                let html = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Fișă Medicală: ${patientName}</h2>
                    <a href="remove_pacient.php?patient_id=${patientId}"
                       class="btn"
                       style="background: #e74c3c; color: white; font-size: 14px;"
                       onclick="return confirm('Sigur vrei să elimini acest pacient? Toate notele și diagnosticul salvat de tine pentru el vor fi șterse definitiv din lista ta.');">
                       Elimină din lista mea
                    </a>
                </div>

                <p><strong> Vârstă:</strong> ${data.age}</p>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

                <form method="POST" action="save_pacient_record.php">
                    <input type="hidden" name="patient_id" value="${patientId}">

                    <label for="diagnosis">📋 Diagnostic medical pus:</label>
                    <textarea id="diagnosis" name="diagnosis" rows="3" placeholder="Introduceți diagnosticul oficial">${data.record.diagnosis}</textarea>

                    <label for="therapist_notes">✍️ Note/Observații Kinetoterapeut (Indicații, evoluție):</label>
                    <textarea id="therapist_notes" name="therapist_notes" rows="4" placeholder="Adăugați note din evoluția tratamentului sau cerințe specifice echipament">${data.record.therapist_notes}</textarea>

                    <button type="submit" class="btn btn-save">💾 Salvează Fișa Pacientului</button>
                </form>

                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
                <h3 style="color: #2980b9;">📅 Programează o nouă ședință</h3>
                <form method="POST" action="process_therapist_booking.php" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <input type="hidden" name="patient_id" value="${patientId}">

                    <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                        <div style="flex: 1;">
                            <label>Data:</label>
                            <input type="date" name="booking_date" required style="width:100%; padding:8px;">
                        </div>
                        <div style="flex: 1;">
                            <label>Ora:</label>
                            <input type="time" name="start_time" required style="width:100%; padding:8px;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Tip procedură:</label>
                            <select name="session_type_id" required style="width:100%; padding:8px;">
                                <option value="">-- Alege procedură --</option>
                                ${data.services.map(service => `<option value="${service.id}">${service.name}</option>`).join('')}
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Sala:</label>
                            <select name="room_id" required style="width:100%; padding:8px;">
                                <option value="">-- Alege sala --</option>
                                ${data.rooms.map(room => `<option value="${room.id}">${room.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="background: #2ecc71; color: white; width: 100%;"> Salvează Programarea Aprobată</button>
                </form>

                <div style="display: flex; gap: 20px; margin-top: 30px;">
                    <div style="flex: 1;">
                        <h4>📅 Ședințe Viitoare</h4>
                        <div id="future_sessions_box"></div>
                    </div>
                    <div style="flex: 1;">
                        <h4>📜 Istoric Ședințe Trecute</h4>
                        <div id="past_sessions_box"></div>
                    </div>
                </div>
            `;

                card.innerHTML = html;

                // Populăm ședințele viitoare
                const futureBox = document.getElementById('future_sessions_box');
                if (data.future.length === 0) {
                    futureBox.innerHTML = '<p style="color:#aaa; font-style:italic;">Nicio ședință programată în viitor.</p>';
                } else {
                    data.future.forEach(s => {
                        let statusColor = s.status === 'approved' ? '#2ecc71' : '#f39c12';
                        futureBox.innerHTML += `
                        <div class="sessions-list">
                            <span class="badge" style="background:${statusColor}">${s.status.toUpperCase()}</span>
                            <strong>${formatDate(s.booking_date)}</strong> la ${s.start_time.substring(0, 5)}<br>
                            <sub>${s.session_name} | Loc: ${s.room_name || 'Nespecificată'}</sub>
                        </div>`;
                    });
                }

                // Populăm istoricul trecut
                const pastBox = document.getElementById('past_sessions_box');
                if (data.past.length === 0) {
                    pastBox.innerHTML = '<p style="color:#aaa; font-style:italic;">Nu există ședințe efectuate în trecut.</p>';
                } else {
                    data.past.forEach(s => {
                        pastBox.innerHTML += `
                        <div class="sessions-list" style="border-left-color: #2ecc71;">
                            <strong>${formatDate(s.booking_date)}</strong> la ${s.start_time.substring(0, 5)}<br>
                            <sub>${s.session_name} | Loc: ${s.room_name || 'Nespecificată'}</sub>
                        </div>`;
                    });
                }
            });
    }

    // Funcție ajutătoare pentru afișarea frumoasă a datei dd.mm.yyyy
    function formatDate(dateStr) {
        const d = new Date(dateStr);
        return ("0" + d.getDate()).slice(-2) + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." + d.getFullYear();
    }
</script>

</body>
</html>