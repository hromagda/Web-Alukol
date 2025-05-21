<?php
namespace App\Models;

use PDO;

class PromoOffer
{
    private $db;

    public function __construct()
    {
        $this->db = get_pdo();
    }

    public function getOffer(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM promo_offer LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOffer(string $content): bool
    {
        $stmt = $this->db->prepare("UPDATE promo_offer SET content = :content WHERE id = 1");
        return $stmt->execute(['content' => $content]);
    }
}