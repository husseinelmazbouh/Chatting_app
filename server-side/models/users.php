<?php
require_once(__DIR__ . "/Model.php");

class User extends Model {
    private int $id;
    private string $full_name;
    private string $email;
    private string $password;
    private string $created_at;
    private bool $is_active;
    protected static string $table = "users";
    public function __construct(array $data) {
        $this->id = (int)$data["id"];
        $this->full_name = $data["full_name"];
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->created_at = $data["created_at"];
        $this->is_active = (bool)$data["is_active"];
    }

    public function getID() { 
        return $this->id; 
    }
    public function getFullName() {  
        return $this->full_name; 
    }
    public function getEmail() { 
        return $this->email; 
    }
    public function getCreatedAt() { 
        return $this->created_at; 
    }
    public function isActive() { 
        return $this->is_active; 
    }

    public static function authenticate(mysqli $connection, string $email, string $password) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        $query = $connection->prepare($sql);
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $user_data = $result->fetch_assoc();
        $query->close();

        if ($user_data && password_verify($password, $user_data['password'])) {
            return new User($user_data);
        }
        return null;
    }

    public function toArray() {
        return [
            "id" => $this->id,
            "full_name" => $this->full_name, 
            "email" => $this->email,
            "created_at" => $this->created_at,
            "is_active" => $this->is_active
        ];
    }
}
?>