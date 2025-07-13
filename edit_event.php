<?php
require 'config.php';
check_auth('admin');

// Récupérer l'événement à éditer
if (!isset($_GET['id'])) {
    header('Location: admin_events.php');
    exit();
}

$event_id = (int)$_GET['id'];
$event = $pdo->prepare("SELECT * FROM evenement WHERE id = ?")->execute([$event_id])->fetch();

if (!$event) {
    header('Location: admin_events.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF");
    }

    // Validation des données
    $required = ['nom', 'club_id', 'localisation', 'date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Le champ $field est requis");
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE evenement SET
                nom = ?,
                club_id = ?,
                localisation = ?,
                date = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['nom'],
            $_POST['club_id'],
            $_POST['localisation'],
            $_POST['date'],
            $event_id
        ]);
        
        $_SESSION['success'] = "Événement mis à jour avec succès";
        header("Location: admin_events.php");
        exit();
        
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// Récupérer la liste des clubs pour le select
$clubs = $pdo->query("SELECT id, nom FROM club")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; padding: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { padding: 10px 20px; border-radius: 4px; text-decoration: none; color: white; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
        .btn-primary { background-color: #3498db; }
        .btn-danger { background-color: #e74c3c; }
        .error { color: #e74c3c; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Modifier l'Événement</h1>
        
        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($event['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="club_id">Club</label>
                <select id="club_id" name="club_id" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= $club['id'] ?>" <?= $club['id'] == $event['club_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($club['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="localisation">Lieu</label>
                <input type="text" id="localisation" name="localisation" value="<?= htmlspecialchars($event['localisation']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="datetime-local" id="date" name="date" 
                       value="<?= date('Y-m-d\TH:i', strtotime($event['date'])) ?>" required>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="admin_events.php" class="btn btn-danger">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</body>
</html>