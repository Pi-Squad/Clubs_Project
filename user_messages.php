<?php
require_once 'config.php';
require_once 'message_config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Récupérer les messages reçus
$stmt = $pdo->prepare("
    SELECT m.*, u.nom as sender_nom, u.prenom as sender_prenom 
    FROM messages m
    JOIN utilisateurs u ON m.sender_id = u.id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$userId]);
$receivedMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les messages envoyés
$stmt = $pdo->prepare("
    SELECT m.*, u.nom as receiver_nom, u.prenom as receiver_prenom 
    FROM messages m
    JOIN utilisateurs u ON m.receiver_id = u.id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$userId]);
$sentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Messages | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        .message-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .message-table th, .message-table td {
            padding: 12px 15px;
            border: 1px solid #eee;
        }
        .message-table th {
            background-color: #f5f6fa;
            color: #2c3e50;
        }
        .message-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message-status {
            font-weight: bold;
        }
        .message-status.unread {
            color: #e74c3c;
        }
        .message-status.read {
            color: #2ecc71;
        }
        .send-btn {
            background-color: #3498db;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .send-btn:hover {
            background-color: #2980b9;
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

    <main class="container">
        <section class="hero">
            <!-- <h1>Messagerie</h1>
            <p>Consultez vos messages reçus et envoyés.</p> -->
            <a href="send_message.php" class="send-btn"><i class="fas fa-paper-plane"></i> Envoyer un message</a>
        </section>

        <section class="clubs-section">
            <h2 class="section-title">Messages Reçus</h2>
            <table class="message-table">
                <thead>
                    <tr>
                        <th>Expéditeur</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receivedMessages as $msg): ?>
                    <tr>
                        <td><?= sanitize($msg['sender_prenom']) . ' ' . sanitize($msg['sender_nom']) ?></td>
                        <td><?= sanitize($msg['subject']) ?></td>
                        <td><?= sanitize($msg['created_at']) ?></td>
                        <td class="message-status <?= $msg['is_read'] ? 'read' : 'unread' ?>">
                            <?= $msg['is_read'] ? 'Lu' : 'Non lu' ?>
                        </td>
                        <td><a href="view_message.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-info">Voir</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($receivedMessages)): ?>
                    <tr><td colspan="5">Aucun message reçu.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="clubs-section">
            <h2 class="section-title">Messages Envoyés</h2>
            <table class="message-table">
                <thead>
                    <tr>
                        <th>Destinataire</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sentMessages as $msg): ?>
                    <tr>
                        <td><?= sanitize($msg['receiver_prenom']) . ' ' . sanitize($msg['receiver_nom']) ?></td>
                        <td><?= sanitize($msg['subject']) ?></td>
                        <td><?= sanitize($msg['created_at']) ?></td>
                        <td><a href="view_message.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-info">Voir</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($sentMessages)): ?>
                    <tr><td colspan="4">Aucun message envoyé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
    </footer>
</body>
</html>
