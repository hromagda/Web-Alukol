<?php

namespace App\Controllers;

use App\Core\View;

/**
 * Kontroler pro zobrazení stránky s nabídkou služeb.
 */
class ServicesController
{
    private array $metaData;

    public function __construct()
    {
        $this->metaData = [
            'title' => 'Nabídka služeb',
            'pageTitle' => 'Naše služby – Alukol montáže a servis',
            'description' => 'Seznam služeb Alukol – montáže, servis a další řešení s hliníkovými profily.',
            'keywords' => 'služby, montáže, servis, hliníkové profily, Alukol',
            'author' => 'Alukol',
            'ogTitle' => 'Nabídka služeb Alukol',
            'ogDescription' => 'Podívejte se na kompletní nabídku služeb montáže a servisu hliníkových profilů od Alukol.',
            'ogImage' => url('obrazky/nahled-fb/services-fb.png'),
            'ogUrl' => url('https://www.alukol.cz/sluzby'),
            'ogType' => 'website',
            'locale' => 'cs_CZ',
        ];
    }

    private function renderPage(array $data = [])
    {
        View::render('services/index', $data, ...array_values($this->metaData));
    }

    /**
     * Zobrazí statickou stránku s přehledem služeb.
     *
     * @return void
     */
    public function index(): void
    {
        $this->renderPage();
    }
}