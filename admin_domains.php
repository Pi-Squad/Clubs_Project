<?php
require 'config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Récupération des domaines
try {
    $domains = $pdo->query("SELECT * FROM domain ORDER BY nom")->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des domaines : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Domaines</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <style>
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

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
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
        text-decoration: none;
        color: white;
        background-color: #3498db;
    }
    .btn:hover {
        background-color: #2980b9;
    }

    /* Actions buttons */
    .actions {
        display: flex;
        gap: 8px;
    }
    .action-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: white;
    }
    .edit-btn {
        background-color: #28a745;
    }
    .edit-btn:hover {
        background-color: #218838;
    }
    .delete-btn {
        background-color: #dc3545;
    }
    .delete-btn:hover {
        background-color: #c82333;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .main-content {
            margin-left: 70px;
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
            <a href="admin_domains.php" class="active">
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
                <!-- Si besoin tu peux ajouter badge ici -->
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1><i class="fas fa-tags"></i> Gestion des Domaines</h1>
                <a href="add_domain.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un domaine
                </a>
            </div>

            <?php if (empty($domains)): ?>
                <p>Aucun domaine enregistré.</p>
            <?php else: ?>
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
                            <td class="actions">
                                <a href="edit_domain.php?id=<?= $domain['id'] ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="delete_domain.php?id=<?= $domain['id'] ?>" 
                                   class="action-btn delete-btn"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce domaine ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>