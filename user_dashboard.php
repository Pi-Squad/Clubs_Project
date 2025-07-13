<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

try {
    // Récupérer les clubs de l'utilisateur
    $stmt_clubs = $pdo->prepare("
        SELECT c.id, c.nom, d.nom as domaine 
        FROM appartient a
        JOIN club c ON a.club_id = c.id
        JOIN Domain d ON c.Domain_id = d.id
        WHERE a.users_id = ?
    ");
    $stmt_clubs->execute([$user['id']]);
    $user_clubs = $stmt_clubs->fetchAll();

    // Récupérer les événements de l'utilisateur
    $stmt_events = $pdo->prepare("
        SELECT e.id, e.nom, e.date, e.localisation, c.nom as club_nom
        FROM amiste a
        JOIN Evenement e ON a.evenement_id = e.id
        JOIN club c ON e.club_id = c.id
        WHERE a.users_id = ?
        ORDER BY e.date DESC
        LIMIT 5
    ");
    $stmt_events->execute([$user['id']]);
    $user_events = $stmt_events->fetchAll();

    // Récupérer des clubs disponibles à recommander
    $stmt_clubs_available = $pdo->prepare("
        SELECT c.id, c.nom, d.nom as domaine 
        FROM club c
        JOIN Domain d ON c.Domain_id = d.id
        WHERE c.id NOT IN (
            SELECT club_id FROM appartient WHERE users_id = ?
        )
        LIMIT 5
    ");
    $stmt_clubs_available->execute([$user['id']]);
    $available_clubs = $stmt_clubs_available->fetchAll();

    // Récupérer le nombre de messages non lus
    $unread_count = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $unread_count->execute([$user['id']]);
    $unread_messages = $unread_count->fetchColumn();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Ajout du style pour le badge de messages */
        .message-badge {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            margin-left: 5px;
            vertical-align: top;
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
                <li><a href="user_dashboard.php" class="active">Accueil</a></li>
                <li><a href="user_clubs.php">Mes Clubs</a></li>
                <li><a href="user_events.php">Mes Événements</a></li>
                <li><a href="user_messages.php">Mes Messages
                    <?php if ($unread_messages > 0): ?>
                        <span class="message-badge"><?= $unread_messages ?></span>
                    <?php endif; ?>
                </a></li>
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
        <section class="hero">
            <h1>Bienvenue, <?= htmlspecialchars($user['prenom']) ?>!</h1>
            <p>Gérez vos clubs et événements sur votre espace personnel.</p>
            <?php if ($unread_messages > 0): ?>
                <div class="notification-bubble">
                    <a href="user_messages.php">
                        <i class="fas fa-envelope"></i> Vous avez <?= $unread_messages ?> message(s) non lu(s)
                    </a>
                </div>
            <?php endif; ?>
        </section>
        
        <section class="clubs-section">
            <h2 class="section-title">Mes Clubs</h2>
            
            <div class="clubs-grid">
                <?php if (count($user_clubs) > 0): ?>
                    <?php foreach ($user_clubs as $club): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <img src="https://source.unsplash.com/random/300x200/?<?= urlencode($club['domaine']) ?>" alt="<?= htmlspecialchars($club['nom']) ?>">
                            </div>
                            <div class="club-info">
                                <h3><?= htmlspecialchars($club['nom']) ?></h3>
                                <p>Domaine: <?= htmlspecialchars($club['domaine']) ?></p>
                                <div class="club-tags">
                                    <span class="tag">Membre</span>
                                </div>
                                <a href="club_details.php?id=<?= $club['id'] ?>" class="view-club-btn">Voir détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Vous n'êtes membre d'aucun club.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="clubs-section">
            <h2 class="section-title">Événements à venir</h2>
            
            <div class="clubs-grid">
                <?php if (count($user_events) > 0): ?>
                    <?php foreach ($user_events as $event): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <img src="https://source.unsplash.com/random/300x200/?event" alt="<?= htmlspecialchars($event['nom']) ?>">
                            </div>
                            <div class="club-info">
                                <div class="event-date">📅 <?= date('d M Y', strtotime($event['date'])) ?></div>
                                <h3><?= htmlspecialchars($event['nom']) ?></h3>
                                <p>Organisé par: <?= htmlspecialchars($event['club_nom']) ?></p>
                                <div class="event-details">
                                    <span>📍 <?= htmlspecialchars($event['localisation']) ?></span>
                                </div>
                                <a href="event_details.php?id=<?= $event['id'] ?>" class="view-club-btn">Détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Aucun événement à venir.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="clubs-section">
            <h2 class="section-title">Clubs recommandés</h2>
            
            <div class="clubs-grid">
                <?php if (count($available_clubs) > 0): ?>
                    <?php foreach ($available_clubs as $club): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <img src="https://source.unsplash.com/random/300x200/?<?= urlencode($club['domaine']) ?>" alt="<?= htmlspecialchars($club['nom']) ?>">
                            </div>
                            <div class="club-info">
                                <h3><?= htmlspecialchars($club['nom']) ?></h3>
                                <p>Domaine: <?= htmlspecialchars($club['domaine']) ?></p>
                                <a href="join_club.php?id=<?= $club['id'] ?>" class="view-club-btn">Rejoindre</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Aucun club recommandé pour l'instant.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
    </footer>
    
    <script src="script.js"></script>
    <script>
        // Animation pour la notification de messages
        document.addEventListener('DOMContentLoaded', function() {
            const messageBadge = document.querySelector('.message-badge');
            if (messageBadge) {
                setInterval(() => {
                    messageBadge.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        messageBadge.style.transform = 'scale(1)';
                    }, 300);
                }, 2000);
            }
            
            const notificationBubble = document.querySelector('.notification-bubble');
            if (notificationBubble) {
                notificationBubble.style.animation = 'pulse 2s infinite';
            }
        });
    </script>
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .notification-bubble {
            display: inline-block;
            background-color: #3a86ff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-top: 1rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .notification-bubble a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</body>
</html>