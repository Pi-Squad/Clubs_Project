<?php
require 'config.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Générer un token CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user = $_SESSION['user'];
$csrf_token = $_SESSION['csrf_token'];

try {
    // Événements où l'utilisateur est déjà inscrit
    $stmt = $pdo->prepare("
        SELECT e.*, c.nom as club_nom
        FROM amiste a
        JOIN Evenement e ON a.evenement_id = e.id
        JOIN club c ON e.club_id = c.id
        WHERE a.users_id = ?
        ORDER BY e.date DESC
    ");
    $stmt->execute([$user['id']]);
    $user_events = $stmt->fetchAll();

    // Événements disponibles pour rejoindre
    $stmt_available = $pdo->prepare("
        SELECT e.*, c.nom as club_nom 
        FROM Evenement e
        JOIN club c ON e.club_id = c.id
        WHERE e.id NOT IN (
            SELECT evenement_id FROM amiste WHERE users_id = ?
        ) AND e.date > NOW()
        ORDER BY e.date ASC
    ");
    $stmt_available->execute([$user['id']]);
    $available_events = $stmt_available->fetchAll();

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Événements | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Style spécifique pour les boutons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            gap: 8px;
        }
        
        /* Bouton Détails */
        .btn-details {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: white;
        }
        
        .btn-details:hover {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(58, 12, 163, 0.3);
        }
        
        /* Bouton Se désinscrire */
        .btn-unregister {
            background: linear-gradient(135deg, #ef233c, #d90429);
            color: white;
        }
        
        .btn-unregister:hover {
            background: linear-gradient(135deg, #d90429, #ef233c);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(217, 4, 41, 0.3);
        }
        
        /* Bouton Rejoindre */
        .btn-join {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }
        
        .btn-join:hover {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.3);
        }
        
        /* Bouton Atelier Tech */
        .btn-workshop {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            color: white;
        }
        
        .btn-workshop:hover {
            background: linear-gradient(135deg, #4895ef, #4cc9f0);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(72, 149, 239, 0.3);
        }
        
        /* Conteneur des boutons */
        .event-actions {
            display: flex;
            gap: 12px;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        /* Icônes */
        .btn-action i {
            font-size: 0.9rem;
        }
        
        /* Styles supplémentaires */
        .clubs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .club-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .club-card:hover {
            transform: translateY(-5px);
        }
        
        .club-image {
            height: 150px;
            background-color: #f1f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .club-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .club-info {
            padding: 1.5rem;
        }
        
        .club-info h3 {
            margin: 0 0 0.5rem;
            color: #2c3e50;
        }
        
        .club-info p {
            margin: 0 0 1rem;
            color: #7f8c8d;
        }
        
        .event-date {
            color: #3498db;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .event-details {
            margin-bottom: 1rem;
            color: #7f8c8d;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            font-style: italic;
            grid-column: 1 / -1;
        }
        
        .search-bar {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .search-bar input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px 0 0 8px;
            font-size: 1rem;
        }
        
        .search-btn {
            padding: 0 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }
        
        .search-btn:hover {
            background: #2980b9;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .event-tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .tag-joined {
            background: #27ae60;
            color: white;
        }
        
        .tag-available {
            background: #3498db;
            color: white;
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
                <li><a href="user_events.php" class="active">Mes Événements</a></li>
                <li><a href="user_messages.php">Messages</a></li>
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
        <!-- <section class="hero">
            <h1>Mes Événements</h1>
            <p>Gérez vos inscriptions et découvrez de nouveaux événements.</p>
        </section> -->
        
        <!-- Section des événements inscrits -->
        <section class="clubs-section">
            <h2 class="section-title">Mes Inscriptions</h2>
            
            <div class="events-filter">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher un événement..." id="search-my-events">
                    <button class="search-btn">Rechercher</button>
                </div>
            </div>

            <div class="clubs-grid" id="my-events-grid">
                <?php if (count($user_events) > 0): ?>
                    <?php foreach ($user_events as $event): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <!-- <img src="https://source.unsplash.com/random/300x200/?event,<?= urlencode($event['nom']) ?>" alt="<?= htmlspecialchars($event['nom']) ?>"> -->
                            </div>
                            <div class="club-info">
                                <div class="event-tag tag-joined">Inscrit</div>
                                <div class="event-date">📅 <?= date('d M Y H:i', strtotime($event['date'])) ?></div>
                                <h3><?= htmlspecialchars($event['nom']) ?></h3>
                                <p>Organisé par: <?= htmlspecialchars($event['club_nom']) ?></p>
                                <div class="event-details">
                                    <span>📍 <?= htmlspecialchars($event['localisation']) ?></span>
                                </div>
                                <div class="event-actions">
                                    <a href="#" class="btn-action btn-workshop"><i class="fas fa-laptop-code"></i> Atelier Tech</a>
                                    <form action="se_desinscrire.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="evenement_id" value="<?= htmlspecialchars($event['id']) ?>">
                                        <button type="submit" class="btn-action btn-unregister"><i class="fas fa-times"></i> Se désinscrire</button>
                                    </form>
                                    <a href="event_details.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn-action btn-details"><i class="fas fa-eye"></i> Détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Vous n'êtes inscrit à aucun événement.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section des événements disponibles -->
        <section class="clubs-section">
            <h2 class="section-title">Événements Disponibles</h2>
            
            <div class="events-filter">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher un événement..." id="search-available-events">
                    <button class="search-btn">Rechercher</button>
                </div>
            </div>

            <div class="clubs-grid" id="available-events-grid">
                <?php if (count($available_events) > 0): ?>
                    <?php foreach ($available_events as $event): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <img src="https://source.unsplash.com/random/300x200/?event,<?= urlencode($event['nom']) ?>" alt="<?= htmlspecialchars($event['nom']) ?>">
                            </div>
                            <div class="club-info">
                                <div class="event-tag tag-available">Disponible</div>
                                <div class="event-date">📅 <?= date('d M Y H:i', strtotime($event['date'])) ?></div>
                                <h3><?= htmlspecialchars($event['nom']) ?></h3>
                                <p>Organisé par: <?= htmlspecialchars($event['club_nom']) ?></p>
                                <div class="event-details">
                                    <span>📍 <?= htmlspecialchars($event['localisation']) ?></span>
                                </div>
                                <div class="event-actions">
                                    <form action="rejoindre_evenement.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="evenement_id" value="<?= htmlspecialchars($event['id']) ?>">
                                        <button type="submit" class="btn-action btn-join"><i class="fas fa-user-plus"></i> Rejoindre</button>
                                    </form>
                                    <a href="event_details.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn-action btn-details"><i class="fas fa-eye"></i> Détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Aucun événement disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
    </footer>
    
    <script>
        // Fonctionnalité de recherche pour mes événements
        document.getElementById('search-my-events').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterEvents(searchTerm, 'my-events-grid');
        });

        // Fonctionnalité de recherche pour événements disponibles
        document.getElementById('search-available-events').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterEvents(searchTerm, 'available-events-grid');
        });

        function filterEvents(searchTerm, gridId) {
            const events = document.querySelectorAll(`#${gridId} .club-card`);
            let hasVisibleResults = false;
            
            events.forEach(event => {
                const text = event.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                event.style.display = isVisible ? 'block' : 'none';
                
                if (isVisible) hasVisibleResults = true;
            });
            
            // Afficher un message si aucun résultat
            const grid = document.getElementById(gridId);
            const noResultsMsg = grid.querySelector('.no-results-msg');
            
            if (!hasVisibleResults) {
                if (!noResultsMsg) {
                    const msg = document.createElement('p');
                    msg.className = 'no-data no-results-msg';
                    msg.textContent = gridId === 'my-events-grid' 
                        ? 'Aucun événement trouvé dans vos inscriptions' 
                        : 'Aucun événement disponible trouvé';
                    grid.appendChild(msg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    </script>
</body>
</html>
