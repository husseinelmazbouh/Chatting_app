<?php
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../models/users.php";

class UserService {
    private $db;
    public function __construct($connection) {
        $this->db = $connection;
    }

    public function register(string $fullName, string $email, string $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
        
        try {
            if ($stmt->execute()) {
                $id = $this->db->insert_id;
                return new User([
                    "id" => $id,
                    "full_name" => $fullName,
                    "email" => $email,
                    "password" => $hashedPassword,
                    "created_at" => date('Y-m-d H:i:s'),
                    "is_active" => true
                ]);
            }
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public function login(string $email, string $password) {
        $user = User::authenticate($this->db, $email, $password);
        
        if ($user) {
            $update = "UPDATE users SET is_active = 1 WHERE id = ?";
            $stmt = $this->db->prepare($update);
            $userId = $user->getID();
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
        return $user;
    }

    public function getAllUsersExcept(int $myId) {
        $sql = "SELECT * FROM users WHERE id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $myId);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new User($row);
        }
        return $users;
    }
    
    public function logout(int $userId) {
        $update = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->prepare($update);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}
?>