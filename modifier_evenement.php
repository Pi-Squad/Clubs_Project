<?php
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de l'événement manquant.");
}

// Récupération de l'événement
$stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    die("Événement introuvable.");
}

// Récupérer la liste des clubs pour la liste déroulante
$clubs = $pdo->query("SELECT id, nom FROM club")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $date = $_POST['date'];
    $lieu = $_POST['lieu'];
    $club_id = $_POST['club_id'];

    $update = $pdo->prepare("UPDATE Evenement SET nom = ?, date = ?, lieu = ?, club_id = ? WHERE id = ?");
    $update->execute([$nom, $date, $lieu, $club_id, $id]);

    header("Location: admin_events.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Événement</title>
</head>
<body>
    <h1>Modifier l'Événement</h1>
    <form method="post">
        <label>Nom :
            <input type="text" name="nom" value="<?= htmlspecialchars($event['nom']) ?>" required>
        </label><br><br>
        <label>Date :
            <input type="date" name="date" value="<?= $event['date'] ?>" required>
        </label><br><br>
        <label>Lieu :
            <input type="text" name="lieu" value="<?= htmlspecialchars($event['localisation']) ?>" required>
        </label><br><br>
        <label>Club :
            <select name="club_id" required>
                <?php foreach ($clubs as $club): ?>
                    <option value="<?= $club['id'] ?>" <?= $club['id'] == $event['club_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($club['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>
        <button type="submit">Enregistrer  </button> 

        <a href="admin_events.php">  Annuler</a>
    </form>
</body>
</html>
