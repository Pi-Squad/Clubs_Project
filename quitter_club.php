<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['club_id'])) {
    $club_id = intval($_POST['club_id']);
    $user_id = $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM appartient WHERE users_id = ? AND club_id = ?");
        $stmt->execute([$user_id, $club_id]);

        header('Location: user_clubs.php');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la sortie du club : " . $e->getMessage());
    }
} else {
    header('Location: user_clubs.php');
    exit();
}
