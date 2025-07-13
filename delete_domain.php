<?php
require 'config.php';
check_auth('admin');

if (!isset($_GET['id'])) {
    header('Location: admin_domains.php');
    exit();
}

$domain_id = (int)$_GET['id'];

try {
    // Vérifier si le domaine est utilisé par des clubs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM club WHERE domain_id = ?");
    $stmt->execute([$domain_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "Impossible de supprimer : ce domaine est utilisé par des clubs";
    } else {
        $pdo->prepare("DELETE FROM domain WHERE id = ?")->execute([$domain_id]);
        $_SESSION['success'] = "Domaine supprimé avec succès";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: admin_domains.php');