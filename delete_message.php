<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

$messageId = $_GET['id'];
$userId = $_SESSION['user']['id'];

// Vérifier si l'utilisateur peut supprimer ce message
$query = "SELECT sender_id, receiver_id FROM messages WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$messageId]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin_messages.php' : 'user_messages.php'));
    exit();
}

// Seul l'expéditeur, le destinataire ou un admin peut supprimer
if ($message['sender_id'] != $userId && $message['receiver_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin_messages.php' : 'user_messages.php'));
    exit();
}

// Supprimer le message
$delete = $pdo->prepare("DELETE FROM messages WHERE id = ?");
$delete->execute([$messageId]);

header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin_messages.php' : 'user_messages.php'));
exit();
?>