<?php
require 'config.php';

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
    $nom = sanitize($_POST['nom']);
    $prenom = sanitize($_POST['prenom'] ?? '');
    $email = sanitize($_POST['email']);
    $password = $_POST['mot_de_passe'] ?? '';

    // Validation des données
    if (empty($nom) || empty($email) || empty($password)) {
        redirect('register.php', 'Tous les champs obligatoires doivent être remplis');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('register.php', 'Adresse email invalide');
    }

    if (strlen($password) < 8) {
        redirect('register.php', 'Le mot de passe doit contenir au moins 8 caractères');
    }

    // Vérifier si l'email existe déjà
    $verif = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $verif->execute([$email]);

    if ($verif->fetch()) {
        redirect('register.php', 'Cet email est déjà utilisé');
    }

    // Hachage du mot de passe
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insertion dans la base avec le rôle par défaut "membre"
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email,  role, password) VALUES (?, ?, ?,'membre', ?)");
    if ($stmt->execute([$nom, $prenom, $email, $password_hash])) {
        redirect('index.php', 'Inscription réussie. Vous pouvez maintenant vous connecter.', 'success');
    } else {
        redirect('register.php', 'Erreur lors de l\'inscription');
    }
}

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $req = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $req->execute([$email]);
    $user = $req->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        redirect($user['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php');
    } else {
        redirect('index.php', 'Email ou mot de passe incorrect');
    }
}

// Traitement de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php', 'Vous avez été déconnecté avec succès', 'success');
}

redirect('index.php');
?>