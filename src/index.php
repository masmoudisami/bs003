<?php
declare(strict_types=1);

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_ROOT', __DIR__);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_ROOT . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    
    $directory = strtolower($parts[0]);
    $filename = $parts[1];
    
    $file = $base_dir . $directory . '/' . $filename . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Pages publiques (pas d'authentification requise)
$public_actions = [
    ['controller' => 'auth', 'action' => 'login'],
    ['controller' => 'auth', 'action' => 'authenticate']
];

$controller_name = $_GET['controller'] ?? 'auth';
$action_name = $_GET['action'] ?? 'login';

// Vérifier si la page est publique
$is_public = false;
foreach ($public_actions as $public) {
    if ($controller_name === $public['controller'] && $action_name === $public['action']) {
        $is_public = true;
        break;
    }
}

// Rediriger vers login si non authentifié et page non publique
if (!$is_public && !App\Controllers\AuthController::check()) {
    header('Location: index.php?controller=auth&action=login');
    exit;
}

// Si déjà authentifié et tentative d'accès à login, rediriger vers dashboard
if ($is_public && App\Controllers\AuthController::check()) {
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$controller_class = 'App\\Controllers\\' . ucfirst($controller_name) . 'Controller';

if (class_exists($controller_class)) {
    $controller = new $controller_class();
    if (method_exists($controller, $action_name)) {
        $controller->$action_name();
    } else {
        http_response_code(404);
        echo "Action non trouvée: " . htmlspecialchars($action_name);
    }
} else {
    http_response_code(404);
    echo "Contrôleur non trouvé: " . htmlspecialchars($controller_class);
}