<?php
namespace App\Models;

use PDO;

class ContactMessage
{
    private $db;

    public function __construct(PDO $pdo = null)
    {
        $this->db = $pdo ?? get_pdo();
    }

    /**
     * Uloží kontaktní zprávu do databáze.
     *
     * @param string $name
     * @param string $email
     * @param string $message
     * @param string|null $phone
     * @param string|null $locality
     * @return bool
     */
    public function save(string $name, string $email, string $message, ?string $phone = null, ?string $locality = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO contact_messages (name, email, message, phone, locality)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $email, $message, $phone, $locality]);
    }

    /**
     * Vrátí všechny zprávy z kontaktního formuláře.
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}