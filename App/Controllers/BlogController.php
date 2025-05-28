<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\BlogArticle;

/**
 * Kontroler pro zobrazení veřejného blogu.
 */
class BlogController
{
    /**
     * Zobrazí seznam všech článků na blogu.
     *
     * @return void
     */
    public function index(): void
    {
        $model = new BlogArticle();
        $articles = $model->getAll();

        View::render('blog/index', [
            'articles' => $articles
        ], 'Blog | Alukol');
    }

    /**
     * Zobrazí konkrétní článek podle slugu.
     *
     * Pokud článek neexistuje, vrátí chybový kód 404.
     *
     * @param string $slug Slug článku (např. "posuvne-zaskleni-terasy").
     * @return void
     */
    public function show(string $slug): void
    {
        $model = new BlogArticle();
        $article = $model->getBySlug($slug);

        if (!$article) {
            http_response_code(404);
            echo "Článek nebyl nalezen.";
            return;
        }

        $pageTitle = $article['title'] . ' | Alukol';
        $pageDescription = mb_substr(strip_tags($article['content']), 0, 150) . '...';
        $pageKeywords = 'pergoly, zasklení, ' . $article['title'];

        View::render('blog/show', [
            'article' => $article,
            'pageTitle' => $pageTitle,
            'metaDescription' => $pageDescription,
            'metaKeywords' => $pageKeywords,
        ]);
    }
}