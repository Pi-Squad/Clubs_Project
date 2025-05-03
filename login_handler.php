<?php
// Start session for user authentication
session_start();

// Database connection configuration
$db_host = 'localhost';
$db_user = 'supnum_user';
$db_pass = 'your_secure_password'; // Change this to a secure password in production
$db_name = 'supnum_club_db';

// Initialize error message variable
$error_message = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard
    header('Location: index.php');
    exit;
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Veuillez remplir tous les champs.';
    } else {
        // Get form data
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        // Validate email
        if (!$email) {
            $error_message = 'Adresse email invalide.';
        } else {
            try {
                // Connect to database
                $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Prepare and execute query
                $stmt = $conn->prepare("SELECT id, email, password, first_name, last_name, role FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                // Check if user exists
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Password is correct, create session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Set remember me cookie if requested
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $token_hash = hash('sha256', $token);
                            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                            
                            // Store token in database
                            $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                            $stmt->bindParam(':user_id', $user['id']);
                            $stmt->bindParam(':token', $token_hash);
                            $stmt->bindParam(':expires_at', date('Y-m-d H:i:s', $expiry));
                            $stmt->execute();
                            
                            // Set cookie
                            setcookie('remember_token', $token, $expiry, '/', '', true, true);
                        }
                        
                        // Log successful login
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                        $stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, status) VALUES (:user_id, :ip_address, 'success')");
                        $stmt->bindParam(':user_id', $user['id']);
                        $stmt->bindParam(':ip_address', $ip_address);
                        $stmt->execute();
                        
                        // Redirect to dashboard
                        header('Location: index.php');
                        exit;
                    } else {
                        $error_message = 'Email ou mot de passe incorrect.';
                        
                        // Log failed login attempt
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                        $stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, status) VALUES (:user_id, :ip_address, 'failed')");
                        $stmt->bindParam(':user_id', $user['id']);
                        $stmt->bindParam(':ip_address', $ip_address);
                        $stmt->execute();
                    }
                } else {
                    $error_message = 'Email ou mot de passe incorrect.';
                    
                    // Log failed login attempt for non-existent user
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $stmt = $conn->prepare("INSERT INTO login_logs (email, ip_address, status) VALUES (:email, :ip_address, 'failed')");
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':ip_address', $ip_address);
                    $stmt->execute();
                }
            } catch (PDOException $e) {
                // Database connection error
                $error_message = 'Erreur de connexion à la base de données. Veuillez réessayer plus tard.';
                
                // Log error (in production, use proper logging system)
                error_log('Login error: ' . $e->getMessage());
            }
        }
    }
}

// Check for "remember me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $token_hash = hash('sha256', $token);
    
    try {
        // Connect to database
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get token from database
        $stmt = $conn->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, u.role 
            FROM remember_tokens rt
            JOIN users u ON rt.user_id = u.id
            WHERE rt.token = :token AND rt.expires_at > NOW()");
        $stmt->bindParam(':token', $token_hash);
        $stmt->execute();
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Valid token, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to dashboard
            header('Location: index.php');
            exit;
        } else {
            // Invalid or expired token, delete cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
    } catch (PDOException $e) {
        // Database connection error, just continue to login page
        error_log('Remember token error: ' . $e->getMessage());
    }
}

// Include the login page HTML
include 'views/login_view.php';
?>