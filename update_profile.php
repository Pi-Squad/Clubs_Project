<?php
require 'config.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $user_id = $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);

        $_SESSION['user']['email'] = $email;

        header('Location: user_profile.php');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
} else {
    header('Location: user_profile.php');
    exit();
}
