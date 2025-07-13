<?php
require 'config.php';
check_auth('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF");
    }

    $nom = trim($_POST['nom'] ?? '');

    if (empty($nom)) {
        $_SESSION['error'] = "Le nom du domaine est requis";
        header("Location: add_domain.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO domain (nom) VALUES (?)");
        $stmt->execute([$nom]);
        
        $_SESSION['success'] = "Domaine créé avec succès";
        header("Location: admin_domains.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: add_domain.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Domaine</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { padding: 10px 20px; border-radius: 4px; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #3498db; border: none; cursor: pointer; }
        .btn-danger { background: #e74c3c; }
        .action-btns { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Ajouter un Domaine</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: #e74c3c; margin-bottom: 20px;">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom du domaine *</label>
                <input type="text" id="nom" name="nom" required autofocus>
            </div>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="action-btns">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="admin_domains.php" class="btn btn-danger">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</body>
</html>