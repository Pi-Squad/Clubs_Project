<?php
require 'config.php';

// 1. VÉRIFICATION DE LA SESSION
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_message'] = [
        'type' => 'error',
        'text' => 'Veuillez vous connecter pour accéder à cette page'
    ];
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

// 2. JOURNALISATION DE L'ACCÈS (pour sécurité)
$log_entry = sprintf(
    "[%s] Accès de %s (ID: %d, Rôle: %s, IP: %s)\n",
    date('Y-m-d H:i:s'),
    $user['email'],
    $user['id'],
    $user['role'],
    $_SERVER['REMOTE_ADDR']
);
file_put_contents('access.log', $log_entry, FILE_APPEND);

// 3. VÉRIFICATION DES AUTORISATIONS
$allowed_roles = ['admin', 'membre']; // Rôles autorisés
if (!in_array($user['role'], $allowed_roles)) {
    session_destroy();
    header('HTTP/1.0 403 Forbidden');
    die('Accès refusé : Rôle non autorisé');
}

// 4. PRÉPARATION DES DONNÉES COMMUNES
$common_data = [
    'user_id' => $user['id'],
    'user_name' => $user['prenom'] . ' ' . $user['nom'],
    'last_login' => $_SESSION['last_login'] ?? date('Y-m-d H:i:s')
];

// 5. REDIRECTION INTELLIGENTE
switch ($user['role']) {
    case 'admin':
        // Charge les données spécifiques aux admins
        $admin_data = $pdo->query("SELECT COUNT(*) as pending_actions FROM audit_log WHERE resolved = 0")->fetch();
        $_SESSION['dashboard_data'] = array_merge($common_data, $admin_data);
        header('Location: admin_dashboard.php');
        break;
        
    case 'membre':
        // Charge les données spécifiques aux membres
        $member_data = $pdo->query(sprintf(
            "SELECT COUNT(*) as club_count FROM appartient WHERE users_id = %d",
            $user['id']
        ))->fetch();
        $_SESSION['dashboard_data'] = array_merge($common_data, $member_data);
        header('Location: user_dashboard.php');
        break;
        
    default:
        header('Location: logout.php?reason=invalid_role');
}

exit();

// 6. FALLBACK VISUEL (si les redirections échouent)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chargement en cours...</title>
    <style>
        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .status-message {
            margin-top: 20px;
            padding: 10px 20px;
            background: #e3f2fd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <h2>Chargement de votre espace personnel</h2>
        <div class="status-message">
            Redirection vers l'interface <?= htmlspecialchars($user['role']) ?>...
        </div>
        <script>
            // Redirection de secours après 3 secondes
            setTimeout(() => {
                window.location.href = "<?= 
                    ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php') 
                ?>";
            }, 3000);
        </script>
    </div>
</body>
</html>