<?php
require 'config.php';
check_auth('admin');

// Initialisation du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération des événements avec jointure
try {
    $events = $pdo->query("
        SELECT e.*, c.nom as club_nom 
        FROM evenement e
        JOIN club c ON e.club_id = c.id
        ORDER BY e.date DESC
    ")->fetchAll();
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* === Réinitialisation et styles de base === */
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

        /* === Layout Dashboard avec Sidebar === */
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

        /* Boutons */
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
            text-decoration: none;
            color: white;
        }
        .btn-primary {
            background-color: #3498db;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
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
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #f1f6fb;
        }
        .badge-primary {
            background-color: #e3f2fd;
            color: #2980b9;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 70px;
                padding: 1.5rem;
            }
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
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Admin Panel</h2>
            <a href="admin_dashboard.php">
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
            <a href="admin_events.php" class="active">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </a>
            <a href="admin_messages.php">
                <i class="fas fa-envelope"></i>
                <span>Messagerie</span>
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
                <h1>Gestion des Événements</h1>
                <a href="add_event.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Ajouter un événement
                </a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Club</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($events) === 0): ?>
                        <tr>
                            <td colspan="6" class="no-data">Aucun événement trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= $event['id'] ?></td>
                            <td><?= htmlspecialchars($event['nom']) ?></td>
                            <td>
                                <span class="badge-primary">
                                    <?= htmlspecialchars($event['club_nom']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($event['date'])) ?></td>
                            <td><?= htmlspecialchars($event['localisation']) ?></td>
                            <td class="actions">
                                <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="delete_event.php?id=<?= $event['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
