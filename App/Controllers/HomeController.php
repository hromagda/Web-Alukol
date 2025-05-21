<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\PromoOffer;

class HomeController
{
    public function index(): void
    {
        View::render('home', [], 'Úvodní stránka');
    }
}