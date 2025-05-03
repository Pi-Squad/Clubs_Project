<?php
// This file contains just the HTML view for the login page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Clubs à SupNum</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-sidebar">
            <div class="login-logo">
                <img src="/api/placeholder/80/80" alt="SupNum Logo" class="logo">
                <h1>SupNum Club Manager</h1>
            </div>
            <div class="login-features">
                <h2>Gérez vos clubs étudiants facilement</h2>
                <ul>
                    <li>Organisation simplifiée des clubs</li>
                    <li>Gestion des événements et activités</li>
                    <li>Suivi des membres et participation</li>
                    <li>Gestion des budgets et ressources</li>
                </ul>
            </div>
        </div>
        
        <div class="login-form-container">
            <div class="login-form-wrapper">
                <h2>Connexion</h2>
                <p>Entrez vos identifiants pour accéder à votre espace</p>
                
                <?php if(isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-password" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember" <?php echo (isset($_POST['remember'])) ? 'checked' : ''; ?>>
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="reset-password.php" class="forgot-password">Mot de passe oublié?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </form>
                
                <div class="register-link">
                    <p>Vous n'avez pas de compte? <a href="register.php">Créer un compte</a></p>
                </div>
            </div>
            
            <div class="login-footer">
                <p>&copy; 2025 SupNum. Tous droits réservés.</p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const toggleButton = document.querySelector('.toggle-password');
            toggleButton.addEventListener('click', function() {
                const passwordField = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    passwordField.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html>