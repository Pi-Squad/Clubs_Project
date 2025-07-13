<?php
require_once 'config.php';
require_once 'message_config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Vérifier l'identifiant du message
$messageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($messageId <= 0) {
    header('Location: user_messages.php');
    exit();
}

// Récupérer le message avec les informations de l'expéditeur et du destinataire
$stmt = $pdo->prepare("
    SELECT m.*, 
           sender.nom AS sender_nom, sender.prenom AS sender_prenom,
           receiver.nom AS receiver_nom, receiver.prenom AS receiver_prenom
    FROM messages m
    JOIN utilisateurs sender ON m.sender_id = sender.id
    JOIN utilisateurs receiver ON m.receiver_id = receiver.id
    WHERE m.id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
");
$stmt->execute([$messageId, $userId, $userId]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    die("Message non trouvé ou accès refusé.");
}

// Marquer comme lu si c'est un message reçu
if ($message['receiver_id'] == $userId && !$message['is_read']) {
    $update = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $update->execute([$messageId]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Message | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        .message-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
        }

        .message-meta {
            margin-bottom: 1.5rem;
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        .message-subject {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .message-content {
            white-space: pre-wrap;
            font-size: 1.1rem;
            line-height: 1.6;
            color: #2c3e50;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <img src="images/supnum.png" alt="Logo SupNum">
            <span>SupClubs</span>
        </div>
        <ul class="nav-links">
            <li><a href="user_dashboard.php">Accueil</a></li>
            <li><a href="user_clubs.php">Mes Clubs</a></li>
            <li><a href="user_events.php">Mes Événements</a></li>
            <li><a href="user_messages.php" class="active">Mes Messages</a></li>
            <li><a href="user_profile.php">Mon Profil</a></li>
        </ul>
        <div class="auth-buttons">
            <span class="user-name"><?= htmlspecialchars($user['prenom']) ?></span>
            <a href="deconnexion.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        <button class="hamburger">☰</button>
    </nav>
</header>

<main>
    <div class="message-container">
        <div class="message-meta">
            <strong>De :</strong> <?= htmlspecialchars($message['sender_prenom'] . ' ' . $message['sender_nom']) ?><br>
            <strong>À :</strong> <?= htmlspecialchars($message['receiver_prenom'] . ' ' . $message['receiver_nom']) ?><br>
            <strong>Date :</strong> <?= date('d M Y à H:i', strtotime($message['created_at'])) ?><br>
            <strong>Statut :</strong> <?= $message['is_read'] ? 'Lu' : 'Non lu' ?>
        </div>

        <h2 class="message-subject"><?= htmlspecialchars($message['subject']) ?></h2>

        <div class="message-content">
            <?= nl2br(htmlspecialchars($message['message'])) ?>
        </div>

        <div class="message-actions">
            <a href="user_messages.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux messages</a>
            <?php if ($message['sender_id'] == $userId): ?>
                <a href="send_message.php?reply=<?= $messageId ?>" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Répondre
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
</footer>
</body>
</html>