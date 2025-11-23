<?php
require_once(__DIR__ . "/../connection/connection.php");

abstract class Model {
    protected static string $table;
    
    public static function find(mysqli $connection, string $id, string $primary_key = "id") {
        $sql = sprintf("SELECT * from %s WHERE %s = ?", static::$table, $primary_key);
        $query = $connection->prepare($sql);
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();
        $data = $result->fetch_assoc();
        $query->close();
        return $data ? new static($data) : null;
    }

    public static function all(mysqli $connection) {
        $sql = sprintf("SELECT * FROM %s", static::$table);
        $query = $connection->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        $items = [];
        while ($data = $result->fetch_assoc()) {
            $items[] = new static($data);
        }
        $query->close();
        return $items;
    }

    public static function where(mysqli $connection, array $conditions) {
        $whereClause = [];
        $types = "";
        $values = [];
        
        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = ?";
            $types .= "s";
            $values[] = $value;
        }
        
        $sql = sprintf("SELECT * FROM %s WHERE %s", static::$table, implode(" AND ", $whereClause));
        $query = $connection->prepare($sql);
        $query->bind_param($types, ...$values);
        $query->execute();
        $result = $query->get_result();
        $items = [];
        while ($data = $result->fetch_assoc()) {
            $items[] = new static($data);
        }
        $query->close();
        return $items;
    }

    public static function create(mysqli $connection, array $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $types = str_repeat("s", count($data));
        
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", static::$table, $columns, $placeholders);
        $query = $connection->prepare($sql);
        $query->bind_param($types, ...array_values($data));
        $query->execute();
        $insert_id = $connection->insert_id;
        $query->close();
        
        return $insert_id;
    }

    public static function update(mysqli $connection, int $id, array $data) {
        $updates = [];
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $types .= "s";
            $values[] = $value;
        }
        
        $types .= "i";
        $values[] = $id;
        
        $sql = sprintf("UPDATE %s SET %s WHERE id = ?", static::$table, implode(", ", $updates));
        $query = $connection->prepare($sql);
        $query->bind_param($types, ...$values);
        $result = $query->execute();
        $query->close();
        
        return $result;
    }

    public static function delete(mysqli $connection, int $id) {
        $sql = sprintf("DELETE FROM %s WHERE id = ?", static::$table);
        $query = $connection->prepare($sql);
        $query->bind_param("i", $id);
        $result = $query->execute();
        $query->close();
        
        return $result;
    }
}
?>