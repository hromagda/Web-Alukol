<?php
namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\ContactController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\ServicesController;

class Router
{
    public function handleRequest(): void
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $segments = explode('/', $uri);

        // Základní routovací tabulka
        $routes = [
            '' => [HomeController::class, 'index'],
            'admin' => [AdminController::class, 'dashboard'],
            'galerie' => [GalleryController::class, 'index'],
            'galerie/load-images' => [GalleryController::class, 'loadImages'],
            'sluzby' => [ServicesController::class, 'index'],
            'kontakt' => [ContactController::class, 'index'],
            'kontakt/odeslat' => [ContactController::class, 'send'],
        ];

        // Nejprve zkusíme klíč jako dvouúrovňový segment
        $routeKey = implode('/', array_slice($segments, 0, 2));
        $route = $routes[$routeKey] ?? null;

        // Pokud nenalezeno, zkusíme první úroveň (např. 'galerie')
        if (!$route) {
            $routeKey = $segments[0] ?? '';
            $route = $routes[$routeKey] ?? null;
        }

        if ($route) {
            [$controllerClass, $method] = $route;
            $controller = new $controllerClass();

            // Parametry (vynecháme první segmenty, které jsme použili jako klíč)
            $paramOffset = substr_count($routeKey, '/');
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