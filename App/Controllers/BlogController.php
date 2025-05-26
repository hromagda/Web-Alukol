<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\BlogArticle;

class BlogController
{
    public function index(): void
    {
        $model = new BlogArticle();
        $articles = $model->getAll();

        View::render('blog/index', [
            'articles' => $articles
        ], 'Blog | Alukol');
    }

    public function show(string $slug): void
    {
        $model = new BlogArticle();
        $article = $model->getBySlug($slug);

        if (!$article) {
            http_response_code(404);
            echo "Článek nebyl nalezen.";
            return;
        }

        View::render('blog/show', [
            'article' => $article
        ], $article['title'] . ' | Alukol');
    }
}