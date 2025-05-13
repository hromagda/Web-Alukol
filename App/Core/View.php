<?php

namespace App\Core;

class View
{
    public static function render(string $viewPath, array $params = [], string $pageTitle = ''): void
    {
        // Získáme cestu k samotnému obsahu stránky
        $viewFile = __DIR__ . '/../../views/' . $viewPath . '.phtml';

        if (!file_exists($viewFile)) {
            throw new \Exception("View soubor $viewPath neexistuje.");
        }

        // Proměnné do šablony
        extract($params);

        // Bufferujeme výstup konkrétního view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Vložíme ho do layoutu
        $pageTitle = $pageTitle ?: 'Alukol';
        include __DIR__ . '/../../views/layout.phtml';
    }
}