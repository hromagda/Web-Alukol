<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\PromoOffer;

/**
 * Kontroler pro zobrazení úvodní stránky webu.
 */
class HomeController
{
    private array $metaData;

    public function __construct()
    {
        $this->metaData = [
            'title' => 'Úvodní stránka',
            'pageTitle' => 'Vítejte na Alukol – montáže a servis hliníkových profilů',
            'description' => 'Alukol nabízí profesionální montáže a servis hliníkových profilů. Kvalita, spolehlivost a rychlá realizace.',
            'keywords' => 'hliníkové profily, montáže, servis, Alukol',
            'author' => 'Alukol',
            'ogTitle' => 'Alukol – kvalitní montáže a servis',
            'ogDescription' => 'Využijte profesionální služby montáže a servisu hliníkových profilů od Alukol.',
            'ogImage' => url('obrazky/nahled-fb/home-fb.png'),
            'ogUrl' => url('https://www.alukol.cz/'),
            'ogType' => 'website',
            'locale' => 'cs_CZ',
        ];
    }

    private function renderPage(array $data = [])
    {
        View::render('home', $data, ...array_values($this->metaData));
    }

    /**
     * Zobrazí úvodní stránku.
     *
     * @return void
     */
    public function index(): void
    {
        $this->renderPage();
    }
}