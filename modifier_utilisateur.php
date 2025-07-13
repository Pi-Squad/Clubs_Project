<?php

require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit();
}

$id = intval($_GET['id']);

try {
    // Récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur introuvable.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $prenom = trim($_POST['prenom']);
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        if (!$prenom || !$nom || !$email || !in_array($role, ['admin', 'membre'])) {
            $error = "Veuillez remplir correctement tous les champs.";
        } else {
            // Mettre à jour l'utilisateur
            $update = $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ?, role = ? WHERE id = ?");
            $update->execute([$prenom, $nom, $email, $role, $id]);
            header('Location: admin_users.php');
            exit();
        }
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f6fa; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 4px; }
        .btn { margin-top: 15px; padding: 10px 15px; background: #3498db; border: none; color: white; border-radius: 5px; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Modifier l'utilisateur</h1>
    <?php if (!empty($error)) : ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Prénom
            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
        </label>
        <label>Nom
            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </label>
        <label>Rôle
            <select name="role" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="membre" <?= $user['role'] === 'membre' ? 'selected' : '' ?>>Membre</option>
            </select>
        </label>
        <button class="btn" type="submit">Enregistrer</button>
    </form>
    <p><a href="admin_users.php">← Retour à la gestion des utilisateurs</a></p>
</body>
</html>
