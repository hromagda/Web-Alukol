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
* @param string $title
* @param string $slug
* @param string $content
* @param string|null $image
* @return bool
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
* @param int $id
* @return array|null
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
* @param int $id
* @param string $title
* @param string $slug
* @param string $content
* @param string|null $image
* @return bool
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
* @param int $id
* @return bool
*/
public function delete(int $id): bool
{
$stmt = $this->db->prepare("DELETE FROM blog_articles WHERE id = :id");
return $stmt->execute(['id' => $id]);
}
}