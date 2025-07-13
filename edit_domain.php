<?php
require 'config.php';
check_auth('admin');

// Initialisation du token CSRF s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id'])) {
    header('Location: admin_domains.php');
    exit();
}

$domain_id = (int)$_GET['id'];

// Requête préparée correctement
$stmt = $pdo->prepare("SELECT * FROM domain WHERE id = ?");
$stmt->execute([$domain_id]);
$domain = $stmt->fetch();

if (!$domain) {
    header('Location: admin_domains.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification robuste du CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Erreur de sécurité (token invalide)";
        header("Location: edit_domain.php?id=$domain_id");
        exit();
    }

    $nom = trim($_POST['nom'] ?? '');

    if (empty($nom)) {
        $_SESSION['error'] = "Le nom du domaine est requis";
        header("Location: edit_domain.php?id=$domain_id");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE domain SET nom = ? WHERE id = ?");
        $stmt->execute([$nom, $domain_id]);
        
        $_SESSION['success'] = "Domaine mis à jour avec succès";
        header("Location: admin_domains.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: edit_domain.php?id=$domain_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Domaine</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .error-message { color: #e74c3c; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
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
        <h1><i class="fas fa-edit"></i> Modifier Domaine</h1>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom du domaine *</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($domain['nom']) ?>" required autofocus>
            </div>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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