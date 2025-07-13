
<?php
require 'config.php';

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Gestion Club</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style1.css" />
    <style>
        /* [Votre CSS existant] */
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .message-error {
            background: #ffecec;
            color: #ff3333;
        }
        .message-success {
            background: #ecfcec;
            color: #33aa33;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-section login-section" id="loginSection">
            <form method="POST" action="traitement.php">
                <h1>Connexion</h1>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message message-<?= $_SESSION['message_type'] ?? 'error' ?>">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                    </div>
                    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                <?php endif; ?>
                
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit" name="connexion">Connexion</button>
                <a href="#" id="forgotPasswordLink">Mot de passe oublié ?</a>
            </form>
        </div>
        
        <div class="switch-container">
            <div class="switch-panel right-panel">
                <h1>Nouveau membre ?</h1>
                <p>Créez un compte pour rejoindre nos clubs</p>
                <a href="register.php"><button>Inscription</button></a>
            </div>
        </div>
    </div>

    <script>
         const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const loginSection = document.getElementById('loginSection');
        const resetPasswordSection = document.getElementById('resetPasswordSection');
        const backToLoginBtn = document.getElementById('backToLoginBtn');
        const resetEmailError = document.getElementById('resetEmailError');

        forgotPasswordLink.addEventListener('click', e => {
            e.preventDefault();
            loginSection.style.display = 'none';
            resetPasswordSection.style.display = 'block';
            resetEmailError.textContent = '';
            document.getElementById('resetEmail').value = '';
        });

        backToLoginBtn.addEventListener('click', () => {
            resetPasswordSection.style.display = 'none';
            loginSection.style.display = 'block';
            resetEmailError.textContent = '';
        });

        document.getElementById('resetPasswordForm').addEventListener('submit', e => {
            e.preventDefault();
            const email = document.getElementById('resetEmail').value.trim();
            resetEmailError.textContent = '';

            if (!email.includes('@supnum.mr')) {
                resetEmailError.textContent = "L'email doit contenir @supnum.mr.";
                return;
            }

            alert(`Un lien de réinitialisation a été envoyé à ${email}.`);
            e.target.reset();
            backToLoginBtn.click();
        });// [Votre JavaScript existant]
    </script>
</body>
</html>