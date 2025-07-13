<?php
require 'config.php';

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Vérification de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF");
    }

    // Récupération et validation des données
    $evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : 0;
    $user_id = $_SESSION['user']['id'];

    if ($evenement_id <= 0) {
        die("ID de l'événement invalide.");
    }

    try {
        // Supprimer l'inscription de l'utilisateur à l'événement
        $stmt = $pdo->prepare("DELETE FROM amiste WHERE users_id = ? AND evenement_id = ?");
        $stmt->execute([$user_id, $evenement_id]);

        // Redirection après succès
        header("Location: user_events.php?message=desinscription_success");
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de la désinscription : " . $e->getMessage());
    }

} else {
    // Accès direct interdit
    header("Location: user_events.php");
    exit();
}
