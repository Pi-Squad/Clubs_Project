<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="register.css" />
    <title>Inscription | Gestion Club</title>
    <style>
        .error-message {
            color: #d93025;
            font-size: 12px;
            margin-top: 4px;
            user-select: text;
        }
        .message-erreur {
            color: #ff3333;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            background: #ffecec;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    
    <div class="main-container">
        <!-- Formulaire Inscription -->
        <div class="form-section register-section">
            <form method="POST" action="traitement.php" id="registerForm">
                <h1>Créer un compte</h1>
                <?php if (isset($_SESSION['erreur_inscription'])): ?>
                    <div class="message-erreur"><?= htmlspecialchars($_SESSION['erreur_inscription']) ?></div>
                    <?php unset($_SESSION['erreur_inscription']); ?>
                <?php endif; ?>
                <input type="text" name="nom" placeholder="Nom complet" required />
                 <input type="text" name="prenom" placeholder="Prenom complet" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="mot_de_passe" id="password" placeholder="Mot de passe" required />
                <input type="password" id="confirmPassword" placeholder="Confirmer le mot de passe" required />
                <div id="passwordError" class="error-message"></div>
                <button type="submit" name="inscription">S'inscrire</button>
            </form>
        </div>

        <!-- Panneau de connexion -->
        <div class="switch-container">
            <div class="switch-panel right-panel">
                <h1>Déjà membre ?</h1>
                <p>Connectez-vous pour accéder à votre espace personnel</p>
                <a href="index.php"><button>Connexion</button></a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');

            registerForm.addEventListener('submit', function(e) {
                // La validation côté serveur dans traitement.php gérera les vérifications principales
                passwordError.style.display = 'none';
            });

            // Validation en temps réel
            passwordInput.addEventListener('input', function() {
                if (this.value.includes('@supnum.mr') && this.value.length >= 8) {
                    passwordError.style.display = 'none';
                }
            });
            
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value === passwordInput.value) {
                    passwordError.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>