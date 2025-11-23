<?php
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../models/users.php";

class UserService {

    public function register(string $fullName, string $email, string $password) {
        global $connection;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
        
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
        return null;
    }

    public function login(string $email, string $password) {
        global $connection;
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
        global $connection;
        $sql = "SELECT * FROM users WHERE id != ? AND is_active = 1";
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
        global $connection;
        $update = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->prepare($update);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}
?>