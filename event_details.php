<?php
require 'config.php';


if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: user_events.php');
    exit();
}

$event_id = intval($_GET['id']);
$stmt = $pdo->prepare("
    SELECT e.*, c.nom AS club_nom
    FROM Evenement e
    JOIN club c ON e.club_id = c.id
    WHERE e.id = ?
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Événement introuvable.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Détails de l'Événement</title>
    <meta charset="UTF-8">
  
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 2rem;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: auto;
        }

        .page-title {
            margin-bottom: 2rem;
            color: #3a0ca3;
        }

        .card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .card-text {
            font-size: 1rem;
            margin-bottom: 0.7rem;
        }

        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.2rem;
            background-color: #3a0ca3;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .btn:hover {
            background-color: #5f2eea;
        }
    </style>


    <link rel="stylesheet" href="style_user.css">

</head>
<body>
    <div class="container">
        <h1 class="page-title">Détails de l'Événement</h1>
        <div class="card">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($event['nom']) ?></h2>
                <p class="card-text"><strong>Club organisateur :</strong> <?= htmlspecialchars($event['club_nom']) ?></p>
                <p class="card-text"><strong>Date :</strong> <?= htmlspecialchars($event['date']) ?></p>
                <p class="card-text"><strong>Localisation :</strong> <?= htmlspecialchars($event['localisation']) ?></p>
                <a href="user_events.php" class="btn">← Retour</a>
            </div>
        </div>
    </div>
</body>

</html>
