<?php
require_once 'config.php';
require_once 'message_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$query = "SELECT m.*, u1.nom as sender_nom, u1.prenom as sender_prenom, 
                 u2.nom as receiver_nom, u2.prenom as receiver_prenom 
          FROM messages m
          JOIN utilisateurs u1 ON m.sender_id = u1.id
          JOIN utilisateurs u2 ON m.receiver_id = u2.id
          ORDER BY m.created_at DESC";
$messages = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages Admin | SupNum Clubs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f5f8fa; margin: 0; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background-color: #34495e; color: white; padding: 2rem 1rem; }
        .sidebar a { color: #ecf0f1; display: block; padding: 10px; border-radius: 6px; text-decoration: none; margin-bottom: 10px; }
        .sidebar a.active, .sidebar a:hover { background-color: #2980b9; }
        .main-content { flex: 1; padding: 2rem; background: #ecf0f1; }
        .main-content h1 { margin-bottom: 1.5rem; }
        .btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 500; }
        .btn-primary { background-color: #3498db; color: white; }
        .btn-danger { background-color: #e74c3c; color: white; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        th, td { padding: 1rem; border-bottom: 1px solid #ddd; }
        th { background: #2980b9; color: white; }
        tbody tr:hover { background-color: #f1f6fb; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <a href="admin_users.php"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="admin_clubs.php"><i class="fas fa-chess-queen"></i> Clubs</a>
        <a href="admin_domains.php"><i class="fas fa-tags"></i> Domaines</a>
        <a href="admin_events.php"><i class="fas fa-calendar-alt"></i> Événements</a>
        <a href="admin_messages.php" class="active"><i class="fas fa-envelope"></i> Messagerie</a>
        <a href="admin_settings.php"><i class="fas fa-cog"></i> Paramètres</a>
        <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>

    <div class="main-content">
        <h1>Gestion des messages</h1>
        <a href="send_message.php" class="btn btn-primary">Envoyer un message</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Sujet</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?= $message['id'] ?></td>
                        <td><?= htmlspecialchars($message['sender_prenom'] . ' ' . $message['sender_nom']) ?></td>
                        <td><?= htmlspecialchars($message['receiver_prenom'] . ' ' . $message['receiver_nom']) ?></td>
                        <td><?= htmlspecialchars($message['subject']) ?></td>
                        <td><?= htmlspecialchars($message['created_at']) ?></td>
                        <td><?= $message['is_read'] ? 'Lu' : 'Non lu' ?></td>
                        <td>
                            <a href="view_message.php?id=<?= $message['id'] ?>" class="btn btn-primary">Voir</a>
                            <a href="delete_message.php?id=<?= $message['id'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
