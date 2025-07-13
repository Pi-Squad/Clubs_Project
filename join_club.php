<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Vérifier si un ID de club est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clubs_disponibles.php");
    exit();
}

$club_id = intval($_GET['id']);

try {
    // Vérifier que l'utilisateur n'est pas déjà membre du club
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM appartient WHERE users_id = ? AND club_id = ?");
    $stmt_check->execute([$user_id, $club_id]);
    $already_member = $stmt_check->fetchColumn();

    if ($already_member) {
        header("Location: user_clubs.php?message=deja_membre");
        exit();
    }

    // Insérer dans la table d'appartenance
    $stmt_insert = $pdo->prepare("INSERT INTO appartient (users_id, club_id) VALUES (?, ?)");
    $stmt_insert->execute([$user_id, $club_id]);

    // Redirection vers les clubs de l'utilisateur
    header("Location: user_clubs.php?message=rejoint");
    exit();
} catch (PDOException $e) {
    echo "Erreur lors de l'inscription au club : " . $e->getMessage();
}
