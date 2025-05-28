<?php

namespace App\Controllers;

use App\Core\View;

/**
 * Kontroler pro zobrazení a načítání galerie realizací.
 */
class GalleryController
{

    /**
     * Cesta ke složce s obrázky galerie.
     *
     * @var string
     */
    // Cesta ke složce s obrázky (např. public/gallery)
    private string $galleryPath = __DIR__ . '/../../public/images/gallery';

    private array $metaData;

    public function __construct()
    {
        $this->metaData = [
            'title' => 'Galerie realizací',
            'pageTitle' => 'Galerie realizací – Alukol montáže a servis hliníkových profilů',
            'description' => 'Prohlédněte si galerii našich realizací hliníkových profilů a služeb. Kvalita a preciznost.',
            'keywords' => 'galerie, realizace, hliníkové profily, servis, Alukol',
            'author' => 'Alukol',
            'ogTitle' => 'Galerie realizací – Alukol',
            'ogDescription' => 'Prohlédněte si galerii našich realizací hliníkových profilů a služeb. Kvalita a preciznost.',
            'ogImage' => url('obrazky/nahled-fb/galerie-fb.png'), // zkontroluj cestu a existenci obrázku
            'ogUrl' => url('https://www.alukol.cz/galerie'),
            'ogType' => 'website',
            'locale' => 'cs_CZ'
        ];
    }

    private function renderGalleryPage(array $data = [])
    {
        // Posíláme metadata jednotlivě jako parametry podle View::render()
        View::render('gallery', $data, ...array_values($this->metaData));
    }


    /**
     * Zobrazí stránku s galerií a připraví výpis náhledových obrázků.
     *
     * @return void
     */
    public function index(): void
    {
        $images = $this->getThumbnails();

        $this->renderGalleryPage([
            'images' => $images
        ]);
    }

    /**
     * Vrací seznam náhledových obrázků (zatím prázdný – připraveno pro budoucí použití).
     *
     * @return array Pole názvů náhledových souborů
     */
    private function getThumbnails(): array
    {
        // Pomocná metoda pro pozdější načítání obrázků
        return [];
    }

    /**
     * Asynchronně načítá HTML s obrázky galerie, které odpovídají konvenci *_nahled.*
     * Vytváří HTML bloky pro lightbox galerii.
     *
     * @return void
     */
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