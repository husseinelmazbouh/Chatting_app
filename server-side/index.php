<?php
require_once __DIR__ . '/services/ResponseService.php';
require_once __DIR__ . '/routes/apis.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route Parsing
$path = $_GET['route'] ?? '/';
if (substr($path, 0, 1) !== '/') {
    $path = '/' . $path;
}

if ($path === '/' || empty($path)) {
    echo ResponseService::response(200, "Chat API Running.");
    exit;
}

if (isset($apis[$path])) {
    $route = $apis[$path];
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
    echo ResponseService::response(404, ["message" => "Route Not Found", "route" => $path]);
}
?>