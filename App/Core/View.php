<?php

namespace App\Core;

use App\Models\PromoOffer;

class View
{
    public static function render(string $viewPath, array $params = [], string $pageTitle = ''): void
    {
        // 👉 Start session, pokud ještě neběží
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Získáme cestu k samotnému obsahu stránky
        $viewFile = __DIR__ . '/../../views/' . $viewPath . '.phtml';

        if (!file_exists($viewFile)) {
            throw new \Exception("View soubor $viewPath neexistuje.");
        }

        // Proměnné do šablony
        extract($params);

        // Načteme promo text z databáze
        $promoModel = new PromoOffer();
        $promoOffer = $promoModel->getOffer(); // Vrací pole nebo false
        $promoContent = $promoOffer['content'] ?? ''; // Vytáhneme pouze text


        // Bufferujeme výstup konkrétního view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Vložíme ho do layoutu
        $pageTitle = $pageTitle ?: 'Alukol';
        include __DIR__ . '/../../views/layout.phtml';
    }
}