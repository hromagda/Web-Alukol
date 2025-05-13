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

        // Rozdělit URI na části (např. ['galerie', '123'])
        $segments = explode('/', $uri);

        // Definujeme základní routy (mohou být bez parametrů)
        $routes = [
            '' => [HomeController::class, 'index'],
            'admin' => [AdminController::class, 'dashboard'],
            'galerie' => [GalleryController::class, 'index'],
            'sluzby' => [ServicesController::class, 'index'],
            'kontakt' => [ContactController::class, 'index'],
        ];

        $routeKey = $segments[0] ?? '';

        if (array_key_exists($routeKey, $routes)) {
            [$controllerClass, $method] = $routes[$routeKey];
            $controller = new $controllerClass();

            // Předáme zbytky z URL jako parametry metodě
            $params = array_slice($segments, 1);

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