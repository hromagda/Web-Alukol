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

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM blog_articles ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_articles WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_articles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, string $title, string $slug, string $content, ?string $image): bool
    {
        if ($image !== null) {
            $sql = "UPDATE blog_articles 
                SET title = :title, slug = :slug, content = :content, image = :image 
                WHERE id = :id";
            $params = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $image,
                'id' => $id,
            ];
        } else {
            $sql = "UPDATE blog_articles 
                SET title = :title, slug = :slug, content = :content 
                WHERE id = :id";
            $params = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'id' => $id,
            ];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

}