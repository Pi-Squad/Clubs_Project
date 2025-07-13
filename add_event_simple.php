<?php
require 'config.php';
check_auth('admin');

// Récupérer la liste des clubs pour le select
$clubs = $pdo->query("SELECT id, nom FROM club ORDER BY nom")->fetchAll();

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
            $_SESSION['error'] = "Le champ " . ucfirst($field) . " est requis";
            header("Location: add_event_simple.php");
            exit();
        }
    }

    try {
        // Insertion dans la table evenement
        $stmt = $pdo->prepare("
            INSERT INTO evenement 
            (nom, club_id, localisation, date, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['nom'],
            $_POST['club_id'],
            $_POST['localisation'],
            $_POST['date']
        ]);

        $_SESSION['success'] = "Événement créé avec succès";
        header("Location: admin_events.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
        header("Location: add_event_simple.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 100px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: var(--danger);
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-calendar-plus"></i> Créer un Événement</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="add_event_simple.php">
            <div class="form-group">
                <label for="nom">Nom de l'événement *</label>
                <input type="text" id="nom" name="nom" required>
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
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="action-buttons">
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