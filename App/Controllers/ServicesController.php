<?php

namespace App\Controllers;

use App\Core\View;

class ServicesController
{
    public function index(): void
    {
        // Zobrazíme statickou stránku s nabídkou služeb
        View::render('services/index', [], 'Nabídka služeb');
    }
}