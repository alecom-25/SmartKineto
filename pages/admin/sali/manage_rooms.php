<?php
require_once __DIR__ . '/../../../init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

if (!isset($db)) {
    die("Eroare: Variabila \$db nu a fost creată în init.php!");
}

//adaugarea unei sali
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $name = trim($_POST['room_name']);
    $type = trim($_POST['room_type']);
    $capacity = (int)$_POST['capacity'];

    if (!empty($name) && $capacity > 0) {
        $stmt = $db->prepare("INSERT INTO rooms (name, type, capacity) VALUES (?, ?, ?)");
        $stmt->execute([$name, $type, $capacity]);
        $_SESSION['admin_msg'] = " Sala '$name' a fost adăugată cu succes!";
    }
    header("Location: manage_rooms.php");
    exit();
}

//stergerea unei sali
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE room_id = ?");
    $checkStmt->execute([$id_to_delete]);
    $is_used = $checkStmt->fetchColumn();

    if ($is_used > 0) {
        // daca este folosita, nu o poate sterge
        $_SESSION['admin_msg'] = " Eroare: Nu poți șterge această sală deoarece există deja $is_used programări în ea";
    } else {
        // altfel o sterge
        $stmt = $db->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id_to_delete]);

        $_SESSION['admin_msg'] = "Sala a fost ștearsă din sistem!";
    }
    header("Location: manage_rooms.php");
    exit();
}

$stmt = $db->query("SELECT r.*, (SELECT COUNT(*) FROM appointments a WHERE a.room_id = r.id 
            AND a.booking_date = CURDATE() AND a.status IN ('approved', 'rescheduled')
            AND HOUR(a.start_time) = HOUR(CURTIME())) as disponibilitate FROM rooms r ORDER BY r.type ASC, r.name ASC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Săli - Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; color: #333; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn { padding: 10px 15px; border-radius: 6px; font-weight: bold; border: none; cursor: pointer; color: white; text-decoration: none; }
        .btn-blue { background: #3498db; }
        .btn-red { background: #e74c3c; font-size: 13px; }
        .btn-back { background: #e9ecef; color: #495057; display: inline-block; margin-bottom: 20px; }

        .form-box { background: #f1f3f5; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        input, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .bg-kineto { background: #9b59b6; color: white; }
        .bg-fitness { background: #e67e22; color: white; }
        .bg-exterior { background: #5f7eea; color: white; }
    </style>
</head>
<body>

<div class="container">
    <a href="../../../dashboard.php" class="btn btn-back">← Înapoi la Dashboard</a>
    <h1>🏢 Gestiune Săli</h1>

    <?php if(isset($_SESSION['admin_msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="form-box">
        <h3>➕ Adaugă o nouă sală/zonă</h3>
        <form method="POST" action="" style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 2;">
                <label>Nume Sală:</label>
                <input type="text" name="room_name" required placeholder="Numele sălii">
            </div>
            <div style="flex: 1;">
                <label>Tip:</label>
                <select name="room_type" required>
                    <option value="fitness">Fitness</option>
                    <option value="kineto">Kinetoterapie</option>
                    <option value="exterior">Exterior</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label>Capacitate max.:</label>
                <input type="number" name="capacity" min="1" required placeholder="Ex: 15">
            </div>
            <div style="flex: 1;">
                <button type="submit" name="add_room" class="btn btn-blue" style="width: 100%; padding: 12px;">Adaugă Sală</button>
            </div>
        </form>
    </div>

    <h3>📋 Sălile înregistrate în sistem</h3>
    <table>
        <thead>
            <tr>
                <th> ID </th>
                <th> Nume Sală </th>
                <th> Tip </th>
                <th> Capacitate Maximă </th>
                <th> Ocupată </th>
                <th> Acțiune </th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($rooms)): ?>
            <tr><td colspan="5" style="text-align:center; color:#777;">Nu există nicio sală în sistem.</td></tr>
        <?php else: ?>
            <?php foreach($rooms as $r): ?>
                <tr>
                    <td><?php echo $r['id']; ?></td>
                    <td><strong><?php echo $r['name']; ?></strong></td>
                    <td>
                            <span class="badge <?php echo $r['type'] === 'kineto' ? 'bg-kineto' : ($r['type'] === 'fitness' ? 'bg-fitness' : 'bg-exterior'); ?>">
                                <?php echo $r['type']; ?>
                            </span>
                    </td>
                    <td>️ <?php echo $r['capacity']; ?> persoane</td>
                    <td>
                        <?php echo $r['disponibilitate']; ?>/<?php echo $r['capacity']; ?>
                    </td>
                    <td>
                        <a href="manage_rooms.php?delete_id=<?php echo $r['id']; ?>" class="btn btn-red" onclick="return confirm('Sigur vrei să ștergi această sală?');"> Șterge</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>