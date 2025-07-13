<?php
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de l'événement manquant.");
}

// Vérifie que l'événement existe
$stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    die("Événement introuvable.");
}

// Suppression
$delete = $pdo->prepare("DELETE FROM Evenement WHERE id = ?");
$delete->execute([$id]);

header("Location: admin_events.php");
exit();
