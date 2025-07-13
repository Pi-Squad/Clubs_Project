
<?php
require 'config.php';

// Vérifie que l'utilisateur est bien un admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$admin_id = $_SESSION['user']['id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        $error = "Utilisateur non trouvé.";
    } elseif (!password_verify($password, $admin['password'])) {
        $error = "Mot de passe actuel incorrect.";
    } else {
        $update_fields = "prenom = ?, nom = ?, email = ?";
        $params = [$prenom, $nom, $email];

        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields .= ", password = ?";
            $params[] = $hashed;
        }

        $params[] = $admin_id;

        $update = $pdo->prepare("UPDATE utilisateurs SET $update_fields WHERE id = ?");
        $update->execute($params);

        $_SESSION['user']['prenom'] = $prenom;
        $_SESSION['user']['nom'] = $nom;
        $_SESSION['user']['email'] = $email;

        $message = "Paramètres mis à jour avec succès.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        <?php include 'admin_styles.css'; ?> /* 👉 À mettre si le style est externalisé */

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f8fa;
            color: #2c3e50;
            margin: 0;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 220px;
            background-color: #34495e;
            color: white;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
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
        }

        .main-content {
            margin-left: 220px;
            padding: 2rem;
            flex: 1;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 600px;
            margin: 2rem auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        form label {
            display: block;
            margin-top: 1rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
        }

        button {
            margin-top: 1.5rem;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 1rem;
        }

        .error {
            text-align: center;
            color: red;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                align-items: center;
                padding: 1rem 0.5rem;
            }

            .sidebar h2,
            .sidebar a span {
                display: none;
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
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
        <a href="admin_users.php"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
        <a href="admin_clubs.php"><i class="fas fa-chess-queen"></i><span>Clubs</span></a>
        <a href="admin_domains.php"><i class="fas fa-tags"></i><span>Domaines</span></a>
        <a href="admin_events.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
        <a href="admin_messages.php"><i class="fas fa-envelope"></i><span>Messagerie</span></a>
        <a href="admin_settings.php" class="active"><i class="fas fa-cog"></i><span>Paramètres</span></a>
        <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
    </nav>

    <!-- Contenu -->
    <div class="main-content">
        <h1 style="text-align:center;">Paramètres du Compte</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Prénom :
                <input type="text" name="prenom" value="<?= htmlspecialchars($_SESSION['user']['prenom']) ?>" required>
            </label>
            <label>Nom :
                <input type="text" name="nom" value="<?= htmlspecialchars($_SESSION['user']['nom']) ?>" required>
            </label>
            <label>Email :
                <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" required>
            </label>
            <label>Mot de passe actuel :
                <input type="password" name="password" required>
            </label>
            <label>Nouveau mot de passe (laisser vide si inchangé) :
                <input type="password" name="new_password">
            </label>

            <button type="submit">Enregistrer les modifications</button>
        </form>
    </div>
</div>
</body>
</html>
