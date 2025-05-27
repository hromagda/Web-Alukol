<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\PromoOffer;

/**
 * Kontroler pro zobrazení úvodní stránky webu.
 */
class HomeController
{
    /**
     * Zobrazí úvodní stránku.
     *
     * @return void
     */
    public function index(): void
    {
        View::render('home', [], 'Úvodní stránka');
    }
}