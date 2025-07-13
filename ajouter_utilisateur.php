<?php
// session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';

    if (!$prenom || !$nom || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'membre'])) {
        $error = "Veuillez remplir tous les champs correctement.";
    } else {
        // Vérifier si email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Un utilisateur avec cet email existe déjà.";
        } else {
            // Insérer en base
            $insert = $pdo->prepare("INSERT INTO utilisateurs (prenom, nom, email, role) VALUES (?, ?, ?, ?)");
            $insert->execute([$prenom, $nom, $email, $role]);
            $success = "Utilisateur ajouté avec succès.";
            // Reset formulaire
            $prenom = $nom = $email = '';
            $role = 'membre';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un utilisateur</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f6fa; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 4px; }
        .btn { margin-top: 15px; padding: 10px 15px; background: #3498db; border: none; color: white; border-radius: 5px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h1>Ajouter un utilisateur</h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Prénom
        <input type="text" name="prenom" value="<?= htmlspecialchars($prenom ?? '') ?>" required>
    </label>
    <label>Nom
        <input type="text" name="nom" value="<?= htmlspecialchars($nom ?? '') ?>" required>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
    </label>
    <label>Rôle
        <select name="role" required>
            <option value="membre" <?= (isset($role) && $role === 'membre') ? 'selected' : '' ?>>Membre</option>
            <option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>Admin</option>
        </select>
    </label>
    <button class="btn" type="submit">Ajouter</button>
</form>

<p><a href="admin_users.php">← Retour à la gestion des utilisateurs</a></p>

</body>
</html>
