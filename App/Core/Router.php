<?php
namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\ContactController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\ServicesController;
use App\Controllers\BlogController;

class Router
{
    public function handleRequest(): void
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = explode('/', $uri);

        // Základní routovací tabulka bez parametrů
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
            'galerie' => [GalleryController::class, 'index'],
            'galerie/load-images' => [GalleryController::class, 'loadImages'],
            'sluzby' => [ServicesController::class, 'index'],
            'kontakt' => [ContactController::class, 'index'],
            'kontakt/odeslat' => [ContactController::class, 'send'],
            'blog' => [BlogController::class, 'index'],
            'blog/detail' => [BlogController::class, 'show'],
        ];

        // Ošetření parametrizovaných rout (např. admin/edit_article/{id}, admin/delete_article/{id})
        // Detekujeme, zda jde o admin/edit_article nebo admin/delete_article s parametrem ID
        if (
            count($segments) === 3 &&
            $segments[0] === 'admin' &&
            in_array($segments[1], ['edit_article', 'delete_article'])
        ) {
            $controller = new AdminController();
            $method = $segments[1] === 'edit_article' ? 'editArticle' : 'deleteArticle';
            $id = $segments[2];

            if (method_exists($controller, $method)) {
                $controller->$method($id);
            } else {
                http_response_code(500);
                echo "Metoda $method neexistuje v AdminController.";
            }
            return;
        }

        // Pro standardní routy bez parametrů
        // Nejprve zkusíme dvouúrovňový klíč (např. admin/login)
        $routeKey = implode('/', array_slice($segments, 0, 2));
        $route = $routes[$routeKey] ?? null;

        // Pokud nenalezeno, zkusíme první segment (např. galerie)
        if (!$route) {
            $routeKey = $segments[0] ?? '';
            $route = $routes[$routeKey] ?? null;
        }

        if ($route) {
            [$controllerClass, $method] = $route;
            $controller = new $controllerClass();

            // Spočítáme počet segmentů ve zvoleném routeKey (počet lomítek)
            $paramOffset = substr_count($routeKey, '/');
            // Všechny segmenty za routeKey považujeme za parametry
            $params = array_slice($segments, $paramOffset + 1);

            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $params);
            } else {
                http_response_code(500);
                echo "Metoda $method neexistuje v kontroleru $controllerClass.";
            }
        } else {
            http_response_code(404);
            echo "Stránka nenalezena.";
        }
    }
}