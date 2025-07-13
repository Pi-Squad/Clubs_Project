<?php
require 'config.php';
check_auth('admin');

if (!isset($_GET['id'])) {
    header('Location: admin_events.php');
    exit();
}

$event_id = (int)$_GET['id'];

try {
    // Vérifier que l'événement existe
    $stmt = $pdo->prepare("SELECT id FROM evenement WHERE id = ?");
    $stmt->execute([$event_id]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Événement introuvable";
        header('Location: admin_events.php');
        exit();
    }

    // Suppression
    $pdo->prepare("DELETE FROM evenement WHERE id = ?")->execute([$event_id]);
    
    $_SESSION['success'] = "Événement supprimé avec succès";
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: admin_events.php');