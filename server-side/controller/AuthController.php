<?php
require_once __DIR__ . "/../../services/UserService.php";
require_once __DIR__ . "/../../connection/connection.php";

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
            echo json_encode(["status" => "error", "message" => "All fields required"]);
            return;
        }

        $user = $this->userService->register($name, $email, $pass);
        if ($user) {
            echo json_encode(["status" => "success", "message" => "Registered"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Email exists"]);
        }
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        $user = $this->userService->login($email, $pass);
        if ($user) {
            echo json_encode([
                "status" => "success",
                "data" => [
                    "user_id" => $user->getID(),
                    "full_name" => $user->getFullName(),
                    "email" => $user->getEmail()
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
        }
    }

    public function logout() {
        $userId = (int)($_POST['user_id'] ?? 0);
        $this->userService->logout($userId);
        echo json_encode(["status" => "success"]);
    }

    // Get Contact List
    public function getContacts() {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId === 0) {
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            return;
        }

        $users = $this->userService->getAllUsersExcept($userId);
        $data = [];
        foreach ($users as $u) {
            $data[] = $u->toArray();
        }
        echo json_encode(["status" => "success", "data" => $data]);
    }
}
?>