<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF");
    }

    $club_id = (int)$_POST['club_id'];
    $user_id = $_SESSION['user']['id'];

    try {
        // Vérifier si l'utilisateur n'est pas déjà membre
        $stmt = $pdo->prepare("SELECT * FROM appartient WHERE users_id = ? AND club_id = ?");
        $stmt->execute([$user_id, $club_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Vous êtes déjà membre de ce club";
        } else {
            // Ajouter l'utilisateur au club
            $stmt = $pdo->prepare("INSERT INTO appartient (users_id, club_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $club_id]);
            $_SESSION['success'] = "Vous avez rejoint le club avec succès";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
}

header('Location: user_clubs.php');