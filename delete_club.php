<?php
require 'config.php';
// session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM club WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: admin_clubs.php');
exit();
