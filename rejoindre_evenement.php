<?php
require 'config.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Vérifier que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité CSRF");
    }

    // Récupérer les données sécurisées
    $evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : 0;
    $user_id = $_SESSION['user']['id'];

    if ($evenement_id <= 0) {
        die("ID de l'événement invalide.");
    }

    try {
        // Vérifier si l'utilisateur est déjà inscrit
        $check = $pdo->prepare("SELECT * FROM amiste WHERE users_id = ? AND evenement_id = ?");
        $check->execute([$user_id, $evenement_id]);

        if ($check->rowCount() > 0) {
            header("Location: user_events.php?message=already_joined");
            exit();
        }

        // Insérer l'inscription
        $stmt = $pdo->prepare("INSERT INTO amiste (users_id, evenement_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $evenement_id]);

        // Redirection avec succès
        header("Location: user_events.php?message=join_success");
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de l'inscription à l'événement : " . $e->getMessage());
    }

} else {
    // Si la requête n'est pas POST, rediriger
    header("Location: user_events.php");
    exit();
}
