<?php
namespace App\Models;

use App\Config\Database;

class Food
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->conn;
    }

    public function all()
    {
        return $this->db->query("SELECT * FROM foods ORDER BY id DESC");
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM foods WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $desc, $cal, $protein, $carbs, $fat, $user_id)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO foods (name, description, calories, protein, carbs, fat, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssiddii", $name, $desc, $cal, $protein, $carbs, $fat, $user_id);
        return $stmt->execute();
    }

    public function update($id, $name, $desc, $cal, $protein, $carbs, $fat)
    {
        $stmt = $this->db->prepare(
            "UPDATE foods SET name=?, description=?, calories=?, protein=?, carbs=?, fat=? WHERE id=?"
        );
        $stmt->bind_param("ssiddii", $name, $desc, $cal, $protein, $carbs, $fat, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM foods WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
