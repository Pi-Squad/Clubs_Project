<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.nom, d.nom AS domaine
        FROM club c
        JOIN Domain d ON c.Domain_id = d.id
        WHERE c.id NOT IN (
            SELECT club_id FROM appartient WHERE users_id = ?
        )
    ");
    $stmt->execute([$user_id]);
    $clubs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors du chargement des clubs disponibles : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Clubs Disponibles | SupNum Clubs</title>
    <link rel="stylesheet" href="style_user.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="navbar">
        <div class="logo"><i class="fas fa-users"></i> SupNum Clubs</div>
        <div class="nav-links">
            <a href="user_dashboard.php"><i class="fas fa-home"></i> Accueil</a>
            <a href="user_clubs.php"><i class="fas fa-chess-queen"></i> Mes Clubs</a>
            <a href="user_events.php"><i class="fas fa-calendar-alt"></i> Mes Événements</a>
            <a href="user_profile.php"><i class="fas fa-user"></i> Mon Profil</a>
        </div>
    </div>
</header>

<main class="main-content">
    <h1 class="page-title"><i class="fas fa-plus-circle"></i> Clubs Disponibles</h1>

    <?php if (count($clubs) > 0): ?>
        <div class="cards-grid">
            <?php foreach ($clubs as $club): ?>
                <div class="card">
                    <img src="https://source.unsplash.com/random/300x200/?<?= urlencode($club['domaine']) ?>" alt="<?= htmlspecialchars($club['nom']) ?>" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($club['nom']) ?></h3>
                        <p class="card-text">Domaine : <?= htmlspecialchars($club['domaine']) ?></p>
                        <a href="join_club.php?id=<?= $club['id'] ?>" class="btn btn-accent">
                            <i class="fas fa-plus"></i> Rejoindre
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center;">Aucun club disponible à rejoindre pour le moment.</p>
    <?php endif; ?>

    <div style="margin-top: 2rem; text-align: center;">
        <a href="user_dashboard.php" class="btn">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
</main>

</body>
</html>
