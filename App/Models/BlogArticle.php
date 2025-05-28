<?php

namespace App\Models;

use PDO;

class BlogArticle
{
    private $db;

    public function __construct()
    {
        $this->db = get_pdo();
    }

    /**
     * Vrátí všechny články z tabulky blog_articles seřazené od nejnovějšího.
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM blog_articles ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vrátí článek podle zadaného slugu.
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_articles WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Vloží nový článek do databáze.
     *
     * @param string $title Nadpis článku
     * @param string $slug Slug článku (unikátní část URL)
     * @param string $content HTML obsah článku
     * @param string|null $image Název souboru s obrázkem nebo null
     * @return bool True při úspěchu, jinak false
     */
    public function insert(string $title, string $slug, string $content, ?string $image): bool
    {
        $stmt = $this->db->prepare("
        INSERT INTO blog_articles (title, slug, content, image, created_at)
        VALUES (:title, :slug, :content, :image, NOW())
        ");
        return $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'image' => $image,
        ]);
    }

    /**
     * Vrátí článek podle ID.
     *
     * @param int $id ID článku
     * @return array|null Asociativní pole s daty článku nebo null, pokud článek neexistuje
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_articles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Aktualizuje článek podle ID.
     *
     * Pokud je $image null, obrázek se nemění.
     *
     * @param int $id ID článku
     * @param string $title Nadpis článku
     * @param string $slug Slug článku
     * @param string $content HTML obsah článku
     * @param string|null $image Název nového obrázku nebo null
     * @return bool True při úspěchu, jinak false
     */
    public function update(int $id, string $title, string $slug, string $content, ?string $image): bool
    {
        if ($image !== null) {
            $sql = "UPDATE blog_articles
            SET title = :title, slug = :slug, content = :content, image = :image
            WHERE id = :id";
            $params = compact('title', 'slug', 'content', 'image', 'id');
        } else {
            $sql = "UPDATE blog_articles
            SET title = :title, slug = :slug, content = :content
            WHERE id = :id";
            $params = compact('title', 'slug', 'content', 'id');
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Smaže článek podle ID.
     *
     * @param int $id ID článku
     * @return bool True při úspěchu, jinak false
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vygeneruje unikátní slug na základě názvu článku.
     *
     * Pokud již slug existuje, přidává číselné přípony, dokud nenajde unikátní variantu.
     *
     * @param string $title Název článku
     * @return string Unikátní slug vhodný pro URL
     */
    public function generateUniqueSlug(string $title): string
    {
        $baseSlug = slugify($title);
        $slug = $baseSlug;
        $i = 1;
        // Zatímco existuje článek se stejným slugem, přidávej číslovku
        while ($this->getBySlug($slug)) {
            $slug = $baseSlug . '-' . $i++;
        }
        return $slug;
    }
}