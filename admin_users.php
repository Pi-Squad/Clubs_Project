<?php
require 'config.php';

// Vérification du rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Gestion recherche
$search = $_GET['search'] ?? '';
$search_sql = '%' . $search . '%';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Nombre total d'utilisateurs filtrés
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE prenom LIKE ? OR nom LIKE ? OR email LIKE ?");
    $countStmt->execute([$search_sql, $search_sql, $search_sql]);
    $total_users = $countStmt->fetchColumn();

    // Récupération des utilisateurs
    $query = "
        SELECT * FROM utilisateurs
        WHERE prenom LIKE ? OR nom LIKE ? OR email LIKE ?
        ORDER BY id DESC
        LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute([$search_sql, $search_sql, $search_sql]);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
}

$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* Reset basique */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Corps */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f8fa;
            color: #2c3e50;
            line-height: 1.6;
        }

        /* Layout dashboard */
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
        .sidebar a span {
            user-select: none;
        }

        /* Main content */
        .main-content {
            margin-left: 220px;
            padding: 2rem;
            flex: 1;
            transition: margin-left 0.3s ease;
        }

        /* Titres */
        h1 {
            margin-bottom: 1.5rem;
            color: #34495e;
        }

        /* Boutons */
        .btn, button {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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

        /* Pagination */
        .pagination {
            margin-top: 20px;
            font-weight: 600;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #3498db;
            transition: color 0.3s ease;
        }
        .pagination a:hover {
            color: #2980b9;
        }
        .pagination strong {
            margin: 0 5px;
            color: #34495e;
        }

        /* Actions */
        .actions a {
            margin-right: 5px;
        }

        /* Form recherche */
        form.search-form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }
        form.search-form button {
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 6px;
            border: none;
            background-color: #3498db;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form.search-form button:hover {
            background-color: #2980b9;
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
            .sidebar a span {
                display: none;
            }
            .sidebar a {
                justify-content: center;
                padding: 12px 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
        <a href="admin_users.php" class="active"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
        <a href="admin_clubs.php"><i class="fas fa-chess-queen"></i><span>Clubs</span></a>
        <a href="admin_domains.php"><i class="fas fa-tags"></i><span>Domaines</span></a>
        <a href="admin_events.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
        <a href="admin_messages.php"><i class="fas fa-envelope"></i><span>Messagerie</span></a>
        <a href="admin_settings.php"><i class="fas fa-cog"></i><span>Paramètres</span></a>
        <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
    </nav>

    <div class="main-content">
        <h1>Gestion des Utilisateurs</h1>

        <p><a href="ajouter_utilisateur.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un utilisateur</a></p>

        <form method="get" class="search-form" action="">
            <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email" value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
        </form>

        <table>
            <thead>
            <tr>
                <th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($users) === 0): ?>
                <tr><td colspan="6">Aucun utilisateur trouvé.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['prenom']) ?></td>
                        <td><?= htmlspecialchars($user['nom']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td class="actions">
                            <a href="modifier_utilisateur.php?id=<?= $user['id'] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Modifier</a>
                            <a href="supprimer_utilisateur.php?id=<?= $user['id'] ?>" 
                               onclick="return confirm('Confirmez la suppression de cet utilisateur ?');" 
                               class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Précédent</a>
                <?php endif; ?>
                <strong>Page <?= $page ?> / <?= $total_pages ?></strong>
                <?php if ($page < $total_pages): ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p><a href="admin_dashboard.php">← Retour au tableau de bord</a></p>
    </div>
</div>
</body>
</html>
