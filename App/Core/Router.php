<?php
namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\ContactController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\ServicesController;
use App\Controllers\BlogController;

/**
 * Třída pro směrování (routing) HTTP požadavků na správné kontrolery a metody.
 */
class Router
{
    /**
     * Zpracuje aktuální HTTP požadavek podle URI a zavolá příslušný kontroler a metodu.
     *
     * Podporuje statické i parametrizované URL (např. {id} nebo {slug}).
     * V případě nenalezení shody vrací HTTP 404.
     * Pokud je metoda neexistující v kontroleru, vrací HTTP 500 s chybovou zprávou.
     *
     * @return void
     */
    public function handleRequest(): void
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $routes = [
            '' => [HomeController::class, 'index'],
            'admin' => [AdminController::class, 'dashboard'],
            'admin/edit_offer' => [AdminController::class, 'editOffer'],
            'admin/login' => [AdminController::class, 'login'],
            'admin/logout' => [AdminController::class, 'logout'],
            'admin/gallery' => [AdminController::class, 'manageGallery'],
            'admin/messages' => [AdminController::class, 'showMessages'],
            'admin/update_article' => [AdminController::class, 'updateArticle'],
            'admin/list_articles' => [AdminController::class, 'listArticles'],
            'admin/create_article' => [AdminController::class, 'createArticle'],
            'admin/save_article' => [AdminController::class, 'saveArticle'],
            'admin/edit_article/{id}' => [AdminController::class, 'editArticle'],
            'admin/delete_article/{id}' => [AdminController::class, 'deleteArticle'],
            'galerie' => [GalleryController::class, 'index'],
            'galerie/load-images' => [GalleryController::class, 'loadImages'],
            'sluzby' => [ServicesController::class, 'index'],
            'kontakt' => [ContactController::class, 'index'],
            'kontakt/odeslat' => [ContactController::class, 'send'],
            'blog' => [BlogController::class, 'index'],
            'blog/detail/{slug}' => [BlogController::class, 'show'],
        ];

        foreach ($routes as $routePattern => $handler) {
            $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $routePattern);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches);
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass();

                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $matches);
                } else {
                    http_response_code(500);
                    echo "Metoda $method neexistuje v $controllerClass.";
                }
                return;
            }
        }

        // 404 fallback
        http_response_code(404);
        echo "<h1>Stránka nenalezena</h1><p><a href='/'>Zpět na úvod</a></p>";
    }
}