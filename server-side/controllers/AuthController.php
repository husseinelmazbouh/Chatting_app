<?php
require_once __DIR__ . "/../services/UserService.php";
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../services/ResponseService.php";

class AuthController {
    private UserService $userService;

    public function __construct() {
        global $connection;
        $this->userService = new UserService($connection);
    }

    public function register() {
        $name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($pass)) {
            echo ResponseService::response(400, "All fields required");
            return;
        }

        $user = $this->userService->register($name, $email, $pass);
        if ($user) {
            echo ResponseService::response(200, "Registered successfully");
        } else {
            echo ResponseService::response(400, "Email already exists");
        }
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

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
        } else {
            echo ResponseService::response(401, "Invalid credentials");
        }
    }

    public function logout() {
        $userId = (int)($_POST['user_id'] ?? 0);
        $this->userService->logout($userId);
        echo ResponseService::response(200, "Logged out");
    }

    public function getContacts() {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId === 0) {
            echo ResponseService::response(401, "Unauthorized");
            return;
        }

        $users = $this->userService->getAllUsersExcept($userId);
        $data = [];
        foreach ($users as $u) {
            $data[] = $u->toArray();
        }
        echo ResponseService::response(200, ["data" => $data]);
    }
}
?>