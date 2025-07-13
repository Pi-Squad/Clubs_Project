<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | SupNum Clubs</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles spécifiques à la page de profil */
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--gradient-1);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid rgba(30, 64, 175, 0.1);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        .btn-save {
            width: 100%;
            padding: 1rem;
            background: var(--gradient-1);
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.2);
        }
        
        .btn-save:hover {
            background: var(--gradient-2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.3);
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 0;
            }
            
            .profile-card {
                padding: 1.5rem;
            }
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
                <li><a href="user_messages.php">Messages</a></li>
                <li><a href="user_profile.php" class="active">Mon Profil</a></li>
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
            <h1>Mon Profil</h1>
            <p>Gérez vos informations personnelles</p>
        </section> -->
        
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                    </div>
                    <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                </div>
                
                <form action="update_profile.php" method="POST">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> SupNum Clubs. Tous droits réservés.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>