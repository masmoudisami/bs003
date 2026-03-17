<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use PDO;

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function login(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        
        include __DIR__ . '/../views/LoginView.php';
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = "Nom d'utilisateur et mot de passe requis";
                header('Location: index.php?controller=auth&action=login');
                exit;
            }
            
            // Récupérer l'utilisateur
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect";
                header('Location: index.php?controller=auth&action=login');
                exit;
            }
            
            $authenticated = false;
            
            // Vérifier si le mot de passe est déjà haché
            if ($user['password_hashed'] == 1) {
                // Mot de passe déjà haché - utiliser password_verify
                if (password_verify($password, $user['password'])) {
                    $authenticated = true;
                }
            } else {
                // Mot de passe en clair - comparer directement et hacher
                if ($password === $user['password']) {
                    $authenticated = true;
                    
                    // Hacher le mot de passe pour les prochaines connexions
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update = $this->db->prepare("UPDATE users SET password = ?, password_hashed = 1 WHERE id = ?");
                    $update->execute([$hashed_password, $user['id']]);
                }
            }
            
            if ($authenticated) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nom_complet'] = $user['nom_complet'];
                $_SESSION['role'] = $user['role'];
                
                $update = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);
                
                $_SESSION['success'] = "Connexion réussie, bienvenue " . htmlspecialchars($user['nom_complet']);
                header('Location: index.php?controller=dashboard&action=index');
                exit;
            } else {
                $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect";
                header('Location: index.php?controller=auth&action=login');
                exit;
            }
        }
        
        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
        $_SESSION['success'] = "Déconnexion réussie";
        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    public function changePassword(): void
    {
        AuthController::requireLogin();
        include __DIR__ . '/../views/ChangePasswordView.php';
    }

    public function updatePassword(): void
    {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: index.php?controller=auth&action=changePassword');
                exit;
            }
            
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "Les nouveaux mots de passe ne correspondent pas";
                header('Location: index.php?controller=auth&action=changePassword');
                exit;
            }
            
            if (strlen($new_password) < 6) {
                $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères";
                header('Location: index.php?controller=auth&action=changePassword');
                exit;
            }
            
            // Vérifier le mot de passe actuel
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $_SESSION['error'] = "Utilisateur non trouvé";
                header('Location: index.php?controller=auth&action=changePassword');
                exit;
            }
            
            // Vérification selon si le mot de passe est haché ou non
            $password_valid = false;
            if ($user['password_hashed'] == 1) {
                if (password_verify($current_password, $user['password'])) {
                    $password_valid = true;
                }
            } else {
                if ($current_password === $user['password']) {
                    $password_valid = true;
                }
            }
            
            if (!$password_valid) {
                $_SESSION['error'] = "Mot de passe actuel incorrect";
                header('Location: index.php?controller=auth&action=changePassword');
                exit;
            }
            
            // Mettre à jour le mot de passe (toujours haché)
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $this->db->prepare("UPDATE users SET password = ?, password_hashed = 1 WHERE id = ?");
            $update->execute([$new_hash, $_SESSION['user_id']]);
            
            $_SESSION['success'] = "Mot de passe modifié avec succès";
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        
        header('Location: index.php?controller=auth&action=changePassword');
        exit;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
}
