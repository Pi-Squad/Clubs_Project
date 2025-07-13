<?php
require 'config.php';
check_auth('admin');

// Récupérer la liste des clubs
$clubs = $pdo->query("SELECT id, nom FROM club ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Erreur de sécurité CSRF");
    }

    // Validation des données
    $required = ['nom', 'club_id', 'date', 'localisation'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Le champ " . ucfirst($field) . " est requis";
            header("Location: add_event.php");
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO evenement 
            (nom, club_id, date, localisation, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['nom'],
            $_POST['club_id'],
            $_POST['date'],
            $_POST['localisation']
        ]);

        $_SESSION['success'] = "Événement créé avec succès";
        header("Location: admin_events.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
        header("Location: add_event.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { padding: 10px 20px; border-radius: 4px; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #3498db; border: none; cursor: pointer; }
        .btn-danger { background: #e74c3c; }
        .action-btns { display: flex; gap: 10px; margin-top: 20px; }
        .error-message { color: #e74c3c; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-calendar-plus"></i> Ajouter un Événement</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom de l'événement *</label>
                <input type="text" id="nom" name="nom" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="club_id">Club organisateur *</label>
                <select id="club_id" name="club_id" required>
                    <option value="">-- Sélectionnez un club --</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Date et heure *</label>
                <input type="datetime-local" id="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="localisation">Lieu *</label>
                <input type="text" id="localisation" name="localisation" required>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="action-btns">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer l'événement
                </button>
                <a href="admin_events.php" class="btn btn-danger">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>

    <script>
        // Définir la date/heure minimale à maintenant
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
            document.getElementById('date').min = localISOTime;
        });
    </script>
</body>
</html>