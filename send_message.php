<?php

require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = false;

// Récupérer la liste des utilisateurs pour le select
$users = [];
if ($_SESSION['user']['role'] === 'admin') {
    $stmt = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs ORDER BY nom, prenom");
} else {
    $stmt = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'admin' ORDER BY nom, prenom");
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer la réponse à un message
$replyTo = null;
if (isset($_GET['reply'])) {
    $stmt = $pdo->prepare("SELECT m.*, u.nom, u.prenom FROM messages m JOIN utilisateurs u ON m.sender_id = u.id WHERE m.id = ?");
    $stmt->execute([$_GET['reply']]);
    $replyTo = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($receiver_id)) {
        $errors[] = "Veuillez sélectionner un destinataire";
    }
    if (empty($subject)) {
        $errors[] = "Le sujet est obligatoire";
    }
    if (empty($message)) {
        $errors[] = "Le message est obligatoire";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user']['id'], $receiver_id, $subject, $message])) {
            $success = true;
            // Réinitialiser les champs si le message est envoyé avec succès
            $subject = '';
            $message = '';
        } else {
            $errors[] = "Une erreur est survenue lors de l'envoi du message";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Envoyer un message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Envoyer un message</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">Message envoyé avec succès!</div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label for="receiver_id" class="form-label">Destinataire</label>
                <select class="form-select" id="receiver_id" name="receiver_id" required>
                    <option value="">Sélectionnez un destinataire</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= isset($replyTo) && $replyTo['sender_id'] == $user['id'] ? 'selected' : '' ?>>
                            <?= $user['prenom'] . ' ' . $user['nom'] ?> (<?= $user['email'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="subject" class="form-label">Sujet</label>
                <input type="text" class="form-control" id="subject" name="subject" required 
                       value="<?= isset($replyTo) ? 'Re: ' . htmlspecialchars($replyTo['subject']) : htmlspecialchars($subject ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Envoyer</button>
            <a href="<?= $_SESSION['user']['role'] === 'admin' ? 'admin_messages.php' : 'user_messages.php' ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>