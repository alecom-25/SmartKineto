<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

$user_id = $_SESSION['user_id'];

// Verificăm dacă membrul are un abonament activ și nesuspendat pentru a-i permite accesul
$stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expires_at >= CURDATE() AND is_suspended = 0 LIMIT 1");
$stmt->execute([$user_id]);
$active_sub = $stmt->fetch();

if (!$active_sub) {
    // Dacă nu are abonament activ, îl trimitem la pagina de planuri
    header("Location: ../abonament/choose_plan.php?error=no_active_sub");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rezervare Ședință - SmartKineto</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; color: #333; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        .step-section { display: none; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .step-section.active { display: block; }

        .btn-choice { padding: 20px; font-size: 18px; cursor: pointer; margin: 10px; width: 220px; border: 2px solid #3498db; background: white; color: #3498db; border-radius: 10px; font-weight: bold; transition: 0.3s; }
        .btn-choice:hover { background: #3498db; color: white; }

        select, input[type="date"] { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; }

        #slots_container { margin-top: 20px; }
        .time-slot { display: inline-block; padding: 12px 20px; margin: 6px; border: 2px solid #3498db; color: #3498db; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .time-slot:hover { background: #3498db; color: white; }
        .time-slot.selected { background: #2ecc71; color: white; border-color: #27ae60; }

        .btn-submit { display: none; width: 100%; margin-top: 25px; padding: 15px; background: #2ecc71; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #27ae60; }
    </style>
</head>
<body>

<div class="container">
    <a href="appointments.php" style="text-decoration: none; color: #666; font-weight: bold;">← Înapoi la Ședințe</a>
    <h1 style="margin-top: 15px;">Rezervă o ședință nouă</h1>

    <div id="step1" class="step-section active">
        <h3>1. Alege tipul ședinței:</h3>
        <button class="btn-choice" onclick="selectType('fitness')">🏋️ Fitness / Workout</button>
        <button class="btn-choice" onclick="selectType('kineto')">🏥 Kinetoterapie</button>
    </div>

    <div id="step2" class="step-section">
        <h3>2. Detalii ședință:</h3>
        <label for="category">Mod desfășurare:</label>
        <select id="category" onchange="checkNextStep()">
            <option value="">-- Selectează --</option>
            <option value="individual">Individual</option>
            <option value="grup">Grup</option>
        </select>

        <label for="location">Locație:</label>
        <select id="location" onchange="checkNextStep()">
            <option value="">-- Selectează --</option>
            <option value="interior">Interior (În sală)</option>
            <option value="exterior">Exterior (În aer liber)</option>
        </select>
    </div>

    <div id="step3" class="step-section">
        <h3>3. Alege Antrenorul și Data:</h3>
        <label for="trainer_id">Antrenor / Specialist disponibil:</label>
        <select id="trainer_id" onchange="fetchSlots()">
            <option value="">-- Mai întâi alege tipul la pasul 1 --</option>
        </select>

        <label for="booking_date">Selectează Data:</label>
        <input type="date" id="booking_date" min="<?php echo date('Y-m-d'); ?>" onchange="fetchSlots()">

        <div id="slots_container">
            <p style="color: #777; font-style: italic;">Selectează un antrenor și o dată validă pentru a vedea orarul liber.</p>
        </div>
    </div>

    <form id="final_form" method="POST" action="process_booking.php">
        <input type="hidden" name="type" id="form_type">
        <input type="hidden" name="category" id="form_category">
        <input type="hidden" name="location" id="form_location">
        <input type="hidden" name="trainer_id" id="form_trainer">
        <input type="hidden" name="date" id="form_date">
        <input type="hidden" name="time" id="form_time">
        <button type="submit" id="submit_btn" class="btn-submit">🎯 Finalizează și Trimite Cererea</button>
    </form>
</div>

<script>
    let selectedType = '';

    // Pasul 1: Selectare Fitness sau Kineto
    function selectType(type) {
        selectedType = type;
        document.getElementById('form_type').value = type;

        document.getElementById('step1').classList.remove('active');
        document.getElementById('step2').classList.add('active');

        // AJAX: Încărcăm antrenorii dinamici (rol: trainer sau kinetoterapeut)
        fetch(`get_trainers.php?type=${type}`)
            .then(response => response.json())
            .then(data => {
                const selectContainer = document.getElementById('trainer_id');
                selectContainer.innerHTML = '<option value="">-- Alege Antrenor --</option>';

                data.forEach(person => {
                    selectContainer.innerHTML += `<option value="${person.id}">${person.nume} ${person.prenume}</option>`;
                });
            })
            .catch(error => console.error("Eroare la aducerea antrenorilor:", error));
    }

    // Pasul 2: Verificare selecturi pentru a debloca pasul 3
    function checkNextStep() {
        const cat = document.getElementById('category').value;
        const loc = document.getElementById('location').value;
        if(cat && loc) {
            document.getElementById('form_category').value = cat;
            document.getElementById('form_location').value = loc;
            document.getElementById('step3').classList.add('active');
        }
    }

    // Pasul 3: Preluare intervale orare disponibile
    function fetchSlots() {
        const trainerId = document.getElementById('trainer_id').value;
        const date = document.getElementById('booking_date').value;

        if(!trainerId || !date) return;

        // AJAX: Încărcăm orele libere calculate de get_slots.php
        fetch(`get_slots.php?trainer_id=${trainerId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('slots_container');

                if (data.length > 0 && data[0] === "lipsa_program") {
                    container.innerHTML = '<p style="color:red; font-weight:bold;">⚠️ Acest antrenor nu are program stabilit pentru această zi.</p>';
                    document.getElementById('submit_btn').style.display = 'none';
                    return;
                }

                container.innerHTML = '<h4>Ore disponibile:</h4>';

                if (data.length === 0) {
                    container.innerHTML += '<p style="color:orange;">Oups! Nu mai sunt intervale libere pentru această dată.</p>';
                    document.getElementById('submit_btn').style.display = 'none';
                    return;
                }

                // Generăm butoanele interactive cu ore
                data.forEach(slot => {
                    const btn = document.createElement('div');
                    btn.className = 'time-slot';
                    btn.innerText = slot;

                    btn.onclick = function() {
                        document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');

                        // Completăm câmpurile ascunse pentru procesare
                        document.getElementById('form_time').value = slot;
                        document.getElementById('form_date').value = date;
                        document.getElementById('form_trainer').value = trainerId;

                        document.getElementById('submit_btn').style.display = 'block';
                    };
                    container.appendChild(btn);
                });
            })
            .catch(error => console.error("Eroare la aducerea orelor:", error));
    }
</script>

</body>
</html>