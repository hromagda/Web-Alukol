<?php

namespace App\Controllers;

use App\Core\View;

/**
 * Kontroler pro zobrazení stránky s nabídkou služeb.
 */
class ServicesController
{
    /**
     * Zobrazí statickou stránku s přehledem služeb.
     *
     * @return void
     */
    public function index(): void
    {
        // Zobrazíme statickou stránku s nabídkou služeb
        View::render('services/index', [], 'Nabídka služeb');
    }
}