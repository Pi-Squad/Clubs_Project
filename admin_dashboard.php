<?php
require 'config.php';
check_auth('admin');

try {
    // Récupération des données
    $users_count = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $clubs_count = $pdo->query("SELECT COUNT(*) FROM club")->fetchColumn();
    $events_count = $pdo->query("SELECT COUNT(*) FROM Evenement")->fetchColumn();
    $domains_count = $pdo->query("SELECT COUNT(*) FROM Domain")->fetchColumn();
    $unread_messages_count = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user']['id']} AND is_read = 0")->fetchColumn();
    
    $domains = $pdo->query("SELECT * FROM Domain")->fetchAll();
    $recent_users = $pdo->query("SELECT * FROM utilisateurs ORDER BY id DESC LIMIT 5")->fetchAll();
    
    // Correction ici - utiliser la bonne colonne de date (date ou date_creation selon votre structure)
    $recent_events = $pdo->query("SELECT e.*, c.nom as club_nom FROM Evenement e JOIN club c ON e.club_id = c.id ORDER BY e.date DESC LIMIT 5")->fetchAll();
    
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données: " . $e->getMessage());
}
?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Reset basique */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f8fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: 220px;
            background-color: #34495e;
            color: white;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: width 0.3s ease;
        }
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            padding-left: 10px;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            margin: 0.5rem 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #2980b9;
            transform: translateX(5px);
        }
        .sidebar i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            color: white;
            background-color: #e74c3c;
            border-radius: 10px;
            margin-left: auto;
        }
        
        /* Main content */
        .main-content {
            margin-left: 220px;
            padding: 2rem;
            flex: 1;
            transition: margin-left 0.3s ease;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e6ed;
        }
        .header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        /* Stat cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 6px;
            height: 100%;
            background: #2980b9;
            border-radius: 12px 0 0 12px;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-weight: 600;
            font-size: 1rem;
            color: #7f8c8d;
        }
        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        .stat-card .icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.8rem;
            color: rgba(41, 128, 185, 0.2);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .card h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            color: #34495e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h2 i {
            color: #2980b9;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        }
        thead {
            background-color: #2980b9;
            color: white;
        }
        th, td {
            padding: 1rem 1.2rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            font-weight: 600;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #f1f6fb;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn i {
            font-size: 0.9em;
        }
        
        /* User info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem 0.5rem;
                align-items: center;
            }
            .sidebar h2,
            .sidebar a span,
            .sidebar .badge {
                display: none;
            }
            .sidebar a {
                justify-content: center;
                padding: 12px 0;
                width: 100%;
            }
            .main-content {
                margin-left: 70px;
                padding: 1.5rem;
            }
            .stats {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .user-info {
                margin-top: 0.5rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card, .card {
            animation: fadeIn 0.5s ease forwards;
        }
        .card:nth-child(2) { animation-delay: 0.1s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Admin Panel</h2>
            <a href="admin_dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="admin_users.php">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="admin_clubs.php">
                <i class="fas fa-chess-queen"></i>
                <span>Clubs</span>
            </a>
            <a href="admin_domains.php">
                <i class="fas fa-tags"></i>
                <span>Domaines</span>
            </a>
            <a href="admin_events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </a>
            <a href="admin_messages.php">
                <i class="fas fa-envelope"></i>
                <span>Messagerie</span>
                <?php if ($unread_messages_count > 0): ?>
                    <span class="badge"><?= $unread_messages_count ?></span>
                <?php endif; ?>
            </a>
            <a href="admin_settings.php">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
            <a href="deconnexion.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Tableau de Bord</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user']['prenom'], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></span>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <h3>Utilisateurs</h3>
                    <p><?= $users_count ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-chess-queen"></i></div>
                    <h3>Clubs</h3>
                    <p><?= $clubs_count ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Événements</h3>
                    <p><?= $events_count ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-tags"></i></div>
                    <h3>Domaines</h3>
                    <p><?= $domains_count ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <h3>Messages non lus</h3>
                    <p><?= $unread_messages_count ?></p>
                </div>
            </div>

            <!-- Derniers utilisateurs -->
            <div class="card">
                <h2><i class="fas fa-user-plus"></i> Derniers Utilisateurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <button class="btn btn-primary"><i class="fas fa-eye"></i> Voir</button>
                                <button class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Derniers événements -->
            <div class="card">
                <h2><i class="fas fa-calendar-check"></i> Derniers Événements</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Lieu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['nom']) ?></td>
                            <td><?= htmlspecialchars($event['club_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($event['date'])) ?></td>
                            <td><?= htmlspecialchars($event['localisation']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Liste des domaines -->
            <div class="card">
                <h2><i class="fas fa-tags"></i> Domaines</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($domains as $domain): ?>
                        <tr>
                            <td><?= $domain['id'] ?></td>
                            <td><?= htmlspecialchars($domain['nom']) ?></td>
                            <td>
                                <button class="btn btn-primary"><i class="fas fa-edit"></i> Modifier</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Animation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Ajouter une classe pour déclencher les animations
            document.body.classList.add('loaded');
            
            // Gestion des messages non lus
            const unreadCount = <?= $unread_messages_count ?>;
            if (unreadCount > 0) {
                // Ajouter une notification visuelle
                const badge = document.createElement('div');
                badge.className = 'notification-badge';
                badge.textContent = unreadCount;
                document.querySelector('.fa-envelope').parentNode.appendChild(badge);
                
                // Faire clignoter l'icône
                setInterval(() => {
                    document.querySelector('.fa-envelope').style.color = 
                        document.querySelector('.fa-envelope').style.color === 'gold' ? 
                        '#ecf0f1' : 'gold';
                }, 1000);
            }
        });
    </script>
</body>
</html>