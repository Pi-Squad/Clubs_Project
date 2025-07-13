<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user']['id'];
    $receiver_id = $_POST['recipient_id'];
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    $original_message_id = $_POST['original_message_id'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, subject, content, original_message_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$sender_id, $receiver_id, $subject, $content, $original_message_id]);
        
        $_SESSION['message_success'] = "Votre réponse a été envoyée avec succès!";
        header("Location: user_messages.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'envoi du message: " . $e->getMessage());
    }
} else {
    header("Location: user_messages.php");
    exit();
}
?>