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
    // Clubs où l'utilisateur est membre
    $stmt = $pdo->prepare("
        SELECT c.*, d.nom as domaine
        FROM appartient a
        JOIN club c ON a.club_id = c.id
        JOIN Domain d ON c.Domain_id = d.id
        WHERE a.users_id = ?
    ");
    $stmt->execute([$user['id']]);
    $user_clubs = $stmt->fetchAll();

    // Clubs disponibles pour rejoindre
    $stmt_available = $pdo->prepare("
        SELECT c.*, d.nom as domaine 
        FROM club c
        JOIN Domain d ON c.Domain_id = d.id
        WHERE c.id NOT IN (
            SELECT club_id FROM appartient WHERE users_id = ?
        )
    ");
    $stmt_available->execute([$user['id']]);
    $available_clubs = $stmt_available->fetchAll();

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Clubs | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles existants conservés */
        .btn-details {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(58, 12, 163, 0.2);
            text-decoration: none;
            gap: 8px;
        }
        
        .btn-details:hover {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(58, 12, 163, 0.3);
        }

        .btn-join {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
            text-decoration: none;
            gap: 8px;
        }
        
        .btn-join:hover {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef233c, #d90429);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(217, 4, 41, 0.2);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #d90429, #ef233c);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(217, 4, 41, 0.3);
        }
        
        .club-actions {
            display: flex;
            gap: 12px;
            margin-top: 1rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            font-style: italic;
            width: 100%;
        }
        
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
        
        .club-tags {
            margin-bottom: 1rem;
        }
        
        .tag {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
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
                <li><a href="user_clubs.php" class="active">Mes Clubs</a></li>
                <li><a href="user_events.php">Mes Événements</a></li>
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
            <h1>Mes Clubs</h1>
            <p>Gérez vos clubs et découvrez de nouvelles communautés.</p>
        </section> -->
        
        <!-- Section des clubs membres -->
        <section class="clubs-section">
            <h2 class="section-title">Mes Clubs</h2>
            
            <div class="clubs-filter">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher un club..." id="search-my-clubs">
                    <button class="search-btn">Rechercher</button>
                </div>
            </div>

            <div class="clubs-grid" id="my-clubs-grid">
                <?php if (count($user_clubs) > 0): ?>
                    <?php foreach ($user_clubs as $club): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <!-- Image du club -->
                                <i class="fas fa-users fa-3x" style="color: #bdc3c7;"></i>
                            </div>
                            <div class="club-info">
                                <h3><?= htmlspecialchars($club['nom']) ?></h3>
                                <p>Domaine: <?= htmlspecialchars($club['domaine']) ?></p>
                                <div class="club-tags">
                                    <span class="tag">Membre</span>
                                </div>
                                <div class="club-actions">
                                    <form action="quitter_club.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="club_id" value="<?= htmlspecialchars($club['id']) ?>">
                                        <button type="submit" class="btn-danger"><i class="fas fa-sign-out-alt"></i> Quitter</button>
                                    </form>
                                    <a href="club_details.php?id=<?= htmlspecialchars($club['id']) ?>" class="btn-details"><i class="fas fa-eye"></i> Détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Vous ne faites partie d'aucun club.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section des clubs disponibles -->
        <section class="clubs-section">
            <h2 class="section-title">Clubs Disponibles</h2>
            
            <div class="clubs-filter">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher un club..." id="search-available-clubs">
                    <button class="search-btn">Rechercher</button>
                </div>
            </div>

            <div class="clubs-grid" id="available-clubs-grid">
                <?php if (count($available_clubs) > 0): ?>
                    <?php foreach ($available_clubs as $club): ?>
                        <div class="club-card">
                            <div class="club-image">
                                <!-- Image du club -->
                                <i class="fas fa-users fa-3x" style="color: #bdc3c7;"></i>
                            </div>
                            <div class="club-info">
                                <h3><?= htmlspecialchars($club['nom']) ?></h3>
                                <p>Domaine: <?= htmlspecialchars($club['domaine']) ?></p>
                                <div class="club-actions">
                                    <form action="rejoindre_club.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="club_id" value="<?= htmlspecialchars($club['id']) ?>">
                                        <button type="submit" class="btn-join"><i class="fas fa-user-plus"></i> Rejoindre</button>
                                    </form>
                                    <a href="club_details.php?id=<?= htmlspecialchars($club['id']) ?>" class="btn-details"><i class="fas fa-eye"></i> Détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Aucun club disponible à rejoindre.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
    </footer>

    <script>
        // Fonctionnalité de recherche pour mes clubs
        document.getElementById('search-my-clubs').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterClubs(searchTerm, 'my-clubs-grid');
        });

        // Fonctionnalité de recherche pour clubs disponibles
        document.getElementById('search-available-clubs').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterClubs(searchTerm, 'available-clubs-grid');
        });

        function filterClubs(searchTerm, gridId) {
            const clubs = document.querySelectorAll(`#${gridId} .club-card`);
            let hasVisibleResults = false;
            
            clubs.forEach(club => {
                const text = club.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                club.style.display = isVisible ? 'block' : 'none';
                
                if (isVisible) hasVisibleResults = true;
            });
            
            // Afficher un message si aucun résultat
            const grid = document.getElementById(gridId);
            const noResultsMsg = grid.querySelector('.no-results-msg');
            
            if (!hasVisibleResults) {
                if (!noResultsMsg) {
                    const msg = document.createElement('p');
                    msg.className = 'no-data no-results-msg';
                    msg.textContent = 'Aucun résultat trouvé';
                    grid.appendChild(msg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    </script>
</body>
</html>