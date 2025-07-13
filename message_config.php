<?php
// Configuration spécifique au module de messagerie

// Constantes pour les paramètres des messages
define('MESSAGE_MAX_SUBJECT_LENGTH', 255);
define('MESSAGE_MAX_CONTENT_LENGTH', 2000);
define('MESSAGES_PER_PAGE', 15);

// Statuts des messages
define('MESSAGE_STATUS_UNREAD', 0);
define('MESSAGE_STATUS_READ', 1);

// Fonctions spécifiques aux messages
function can_view_message($message, $user_id) {
    // Vérifie si l'utilisateur peut voir le message
    return $message['sender_id'] == $user_id || 
           $message['receiver_id'] == $user_id ||
           is_admin();
}

function get_message_status_label($status) {
    $statuses = [
        MESSAGE_STATUS_UNREAD => 'Non lu',
        MESSAGE_STATUS_READ => 'Lu'
    ];
    return $statuses[$status] ?? 'Inconnu';
}

// Protection spécifique pour le contenu des messages
function sanitize_message($content) {
    $allowed_tags = '<p><br><a><strong><em><ul><ol><li>';
    return strip_tags(trim($content), $allowed_tags);
}
?>