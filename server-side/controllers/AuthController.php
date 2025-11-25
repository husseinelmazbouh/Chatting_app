<?php
require_once __DIR__ . "/../services/UserService.php";
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../services/ResponseService.php";

class AuthController {
    private UserService $userService;

    public function __construct() {
        global $connection;
        if (!$connection) {
                echo ResponseService::response(500, "Database connection failed.");
                die();
        }
        $this->userService = new UserService($connection);
    }

    private function getRequestData() {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        
        if (is_array($json)) return $json;
        if (!empty($_POST)) return $_POST;
        return [];
    }

    public function register() {
        $data = $this->getRequestData(); 

        $name = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $pass = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($pass)) {
            echo ResponseService::response(400, "All fields required.");
            exit; 
        }

        $user = $this->userService->register($name, $email, $pass);
        
        if ($user) {
            echo ResponseService::response(200, "Registered successfully");
            exit;
        } else {
            echo ResponseService::response(400, "Email already exists");
            exit;
        }
    }

    public function login() {
        $data = $this->getRequestData();
        $email = $data['email'] ?? '';
        $pass = $data['password'] ?? '';

        if (empty($email) || empty($pass)) {
            echo ResponseService::response(400, "Email and Password required");
            exit;
        }

        $user = $this->userService->login($email, $pass);
        if ($user) {
            echo ResponseService::response(200, [
                "message" => "Login successful",
                "data" => [
                    "user_id" => $user->getID(),
                    "full_name" => $user->getFullName(),
                    "email" => $user->getEmail()
                ]
            ]);
            exit;
        } else {
            echo ResponseService::response(401, "Invalid credentials");
            exit;
        }
    }
    public function logout() {
        $data = $this->getRequestData();
        $userId = (int)($data['user_id'] ?? 0);
        $this->userService->logout($userId);
        echo ResponseService::response(200, "Logged out");
        exit;
    }

    public function getContacts() {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId === 0) {
            echo ResponseService::response(401, "Unauthorized");
            exit;
        }

        $users = $this->userService->getAllUsersExcept($userId);
        $data = [];
        foreach ($users as $u) {
            $data[] = $u->toArray();
        }
        echo ResponseService::response(200, ["data" => $data]);
        exit;
    }
}
?>