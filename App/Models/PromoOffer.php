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

    /**
     * Vrátí aktuální promo nabídku (očekává jeden řádek v tabulce).
     *
     * @return array|null
     */
    public function getOffer(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM promo_offer LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Aktualizuje text promo nabídky.
     *
     * @param string $content
     * @return bool
     */
    public function updateOffer(string $content): bool
    {
        $cleanContent = strip_tags($content); // pro jistotu i tady
        $stmt = $this->db->prepare("UPDATE promo_offer SET content = :content WHERE id = 1");
        return $stmt->execute(['content' => $cleanContent]);
    }
}