<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: user_clubs.php');
    exit();
}

$club_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT c.*, d.nom AS domaine_nom, 
               (SELECT COUNT(*) FROM appartient WHERE club_id = c.id) AS membres_count
        FROM club c
        JOIN Domain d ON c.Domain_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$club_id]);
    $club = $stmt->fetch();

    if (!$club) {
        throw new Exception("Club introuvable");
    }

    // Récupérer les événements à venir
    $events_stmt = $pdo->prepare("
        SELECT * FROM evenement 
        WHERE club_id = ? AND date >= NOW()
        ORDER BY date ASC
        LIMIT 3
    ");
    $events_stmt->execute([$club_id]);
    $events = $events_stmt->fetchAll();

    // Récupérer les membres
    $members_stmt = $pdo->prepare("
        SELECT u.id, u.prenom, u.nom, u.email
        FROM appartient a
        JOIN utilisateurs u ON a.users_id = u.id
        WHERE a.club_id = ?
        LIMIT 5
    ");
    $members_stmt->execute([$club_id]);
    $members = $members_stmt->fetchAll();

} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($club['nom']) ?> | SupNum Clubs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --secondary: #4361ee;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7ff;
            color: var(--dark);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .club-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(58, 12, 163, 0.1);
            position: relative;
            overflow: hidden;
        }
        .club-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="white" opacity="0.05"><path d="M30,50 Q50,30 70,50 T90,50"></path></svg>');
            background-size: contain;
        }
        .club-title {
            font-size: 2.5rem;
            margin: 0;
            font-weight: 700;
        }
        .club-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            align-items: center;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-top: 20px;
            transition: opacity 0.3s;
        }
        .back-btn:hover {
            opacity: 0.8;
        }
        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        }
        .section-title {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--light);
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        .event-date {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .description {
            line-height: 1.6;
            color: #555;
        }
        @media (max-width: 768px) {
            .club-title {
                font-size: 2rem;
            }
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="club-header">
            <a href="user_clubs.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour aux clubs
            </a>
            <h1 class="club-title"><?= htmlspecialchars($club['nom']) ?></h1>
            <div class="club-meta">
                <span class="badge">
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($club['domaine_nom']) ?>
                </span>
                <span class="badge">
                    <i class="fas fa-users"></i> <?= $club['membres_count'] ?> membres
                </span>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i> Description
            </h2>
            <p class="description">
                <?= nl2br(htmlspecialchars($club['description'] ?? 'Ce club n\'a pas encore de description.')) ?>
            </p>
        </div>

        <?php if (!empty($events)): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i> Événements à venir
            </h2>
            <div class="grid">
                <?php foreach ($events as $event): ?>
                <div class="card">
                    <div class="event-date">
                        <i class="far fa-calendar"></i>
                        <?= date('d M Y', strtotime($event['date'])) ?>
                    </div>
                    <h3><?= htmlspecialchars($event['nom']) ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['localisation']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($members)): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-users"></i> Membres actifs
            </h2>
            <div class="grid">
                <?php foreach ($members as $member): ?>
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="member-avatar">
                            <?= strtoupper(substr($member['prenom'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 style="margin: 0;"><?= htmlspecialchars($member['prenom'] . ' ' . $member['nom']) ?></h3>
                            <small><?= htmlspecialchars($member['email']) ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>