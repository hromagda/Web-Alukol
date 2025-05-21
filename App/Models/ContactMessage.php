<?php

namespace App\Models;
use PDO;

class ContactMessage
{
    private $db;

    public function __construct()
    {
        $this->db = get_pdo();
    }
    public function save($name, $email, $message, $phone = null, $locality = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO contact_messages (name, email, message, phone, locality)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $email, $message, $phone, $locality]);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}