<?php
// session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit();
}

$id = intval($_GET['id']);

try {
    $delete = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $delete->execute([$id]);
} catch (PDOException $e) {
    die("Erreur lors de la suppression : " . $e->getMessage());
}

header('Location: admin_users.php');
exit();
