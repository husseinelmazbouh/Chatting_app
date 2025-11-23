<?php
require_once __DIR__ . '/services/ResponseService.php';
require_once __DIR__ . '/routes/apis.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$request_uri = $_SERVER['REQUEST_URI']; 
$script_name = $_SERVER['SCRIPT_NAME']; 
$script_dir = dirname($script_name);

$path = parse_url($request_uri, PHP_URL_PATH);

if (strpos($path, $script_dir) === 0) {
    $path = substr($path, strlen($script_dir));
}

if (strpos($path, '/index.php') === 0) {
    $path = substr($path, 10);
}

$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/');

if (empty($path) || $path === '/') {
    echo ResponseService::response(200, "API is running!");
    exit;
}

if (isset($apis[$path])) {
    $route = $apis[$path];

    if (!isset($route['controller']) || !isset($route['method'])) {
        echo ResponseService::response(500, "Invalid Route Config");
        exit;
    }

    $controller_name = $route['controller'];
    $method = $route['method'];
    
    $controller_file = __DIR__ . "/controllers/{$controller_name}.php";

    if (file_exists($controller_file)) {
        require_once $controller_file;
        
        if (class_exists($controller_name)) {
            $controller = new $controller_name();
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                echo ResponseService::response(500, "Method '$method' not found");
            }
        } else {
            echo ResponseService::response(500, "Class '$controller_name' not found");
        }
    } else {
        echo ResponseService::response(500, "Controller file missing: $controller_name");
    }
} else {
    echo ResponseService::response(404, [
        "message" => "Route Not Found",
        "requested_path" => $path
    ]);
}
?>