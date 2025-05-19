<?php

namespace App\Controllers;

use App\Core\View;

class GalleryController
{
    // Cesta ke složce s obrázky (např. public/gallery)
    private string $galleryPath = __DIR__ . '/../../public/images/gallery';

    public function index(): void
    {
        $images = $this->getThumbnails();

        // Načti view galerie (zatím bez obrázků)
        View::render('gallery', [
            'images' => $images
        ], 'Galerie realizací');
    }

    private function getThumbnails(): array
    {
        // Pomocná metoda pro pozdější načítání obrázků
        return [];
    }

    public function loadImages(): void
    {
        $html = '';

        if (!is_dir($this->galleryPath)) {
            http_response_code(500);
            echo '<p>Složka galerie neexistuje.</p>';
            return;
        }

        $files = scandir($this->galleryPath);
        foreach ($files as $file) {
            if (preg_match('/_nahled\.(jpg|jpeg|png|gif)$/i', $file)) {
                $full = str_replace('_nahled.', '.', $file);

                // ✨ Ověření, že plný obrázek existuje
                $fullPath = $this->galleryPath . '/' . $full;
                if (!file_exists($fullPath)) continue;

                $html .= '<div class="gallery-item">';
                $html .= '<a href="/images/gallery/' . $full . '" data-lightbox="galerie" rel="lightbox">';
                $html .= '<img src="/images/gallery/' . $file . '" loading="lazy" alt="Realizace" class="img-fluid">';
                $html .= '</a></div>';
            }
        }

        header('Content-Type: text/html');
        echo $html;
    }
}