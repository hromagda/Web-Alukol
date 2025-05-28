<?php

namespace App\Core;

use App\Models\PromoOffer;

/**
 * Třída pro vykreslování šablon (view) s podporou layoutu a předávání dat.
 */
class View
{
    /**
     * Vykreslí konkrétní view šablonu s předanými daty a vloží ji do hlavního layoutu.
     *
     * Načte promo text z databáze (např. "Akční nabídka") a předá ho do layoutu.
     * Titulek stránky a další meta informace lze nastavit pomocí proměnných v poli $params.
     *
     * @param string $viewPath Cesta k souboru view relativně k adresáři `views/` bez přípony `.phtml` (např. 'home/index')
     * @param array $params Asociativní pole dat, která se extrahují do šablony jako proměnné
     * @param string $pageTitle Titulek stránky; pokud není zadán, použije se výchozí 'Alukol'
     *
     * @throws \Exception Pokud view soubor neexistuje
     *
     * @return void
     */
    public static function render(string $viewPath, array $params = [], string $pageTitle = ''): void
    {
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

        $metaDescription = $metaDescription ?? 'Alukol se specializuje na montáž hliníkových profilů...';
        $metaKeywords = $metaKeywords ?? 'hliníkové profily, žaluzie, pergoly...';
        $metaAuthor = $metaAuthor ?? 'Alukol';

        $ogTitle = $ogTitle ?? $pageTitle;
        $ogDescription = $ogDescription ?? $metaDescription;
        $ogImage = $ogImage ?? url('obrazky/nahled-fb/fb.png');
        $ogUrl = $ogUrl ?? 'https://www.alukol.cz';
        $ogType = $ogType ?? 'website';
        $ogLocale = $ogLocale ?? 'cs_CZ';
        include __DIR__ . '/../../views/layout.phtml';
    }
}