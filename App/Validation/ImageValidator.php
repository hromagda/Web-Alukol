<?php

namespace App\Validation;

/**
 * Třída pro validaci obrázků.
 *
 * Obsahuje metody pro ověření MIME typu, velikosti, rozměrů a přípony souboru.
 */
class ImageValidator
{
    /**
     * Povolené MIME typy obrázků.
     *
     * @var string[]
     */
    public static array $defaultAllowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    /**
     * Ověří MIME typ obrázku podle skutečného obsahu souboru.
     *
     * @param string $filePath Cesta k souboru.
     * @param string[]|null $allowedTypes Pole povolených MIME typů, pokud není zadáno, použije se výchozí.
     * @return bool Vrací true, pokud MIME typ souboru patří mezi povolené, jinak false.
     */
    public static function isValidMimeType(string $filePath, array $allowedTypes = null): bool
    {
        $allowedTypes = $allowedTypes ?? self::$defaultAllowedTypes;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        return in_array($mimeType, $allowedTypes, true);
    }

    /**
     * Ověří, že velikost souboru nepřekračuje maximální povolenou velikost.
     *
     * @param string $filePath Cesta k souboru.
     * @param int $maxBytes Maximální velikost v bytech (výchozí 5 MB).
     * @return bool Vrací true, pokud je soubor menší nebo roven maximu, jinak false.
     */
    public static function isBelowMaxSize(string $filePath, int $maxBytes = 5_000_000): bool
    {
        return filesize($filePath) <= $maxBytes;
    }

    /**
     * Ověří, že rozměry obrázku nepřekračují dané maximální hodnoty.
     *
     * @param string $filePath Cesta k souboru.
     * @param int $maxWidth Maximální šířka v pixelech (výchozí 3000).
     * @param int $maxHeight Maximální výška v pixelech (výchozí 2000).
     * @return bool Vrací true, pokud jsou rozměry obrázku v povoleném rozsahu, jinak false.
     */
    public static function isWithinDimensions(string $filePath, int $maxWidth = 3000, int $maxHeight = 2000): bool
    {
        [$width, $height] = getimagesize($filePath) ?: [0, 0];

        return $width <= $maxWidth && $height <= $maxHeight;
    }

    /**
     * Ověří, zda má soubor platnou příponu.
     *
     * @param string $fileName Název souboru.
     * @param string[] $allowedExtensions Pole povolených přípon (výchozí ['jpg', 'jpeg', 'png', 'gif', 'webp']).
     * @return bool Vrací true, pokud přípona souboru patří mezi povolené, jinak false.
     */
    public static function hasValidExtension(string $fileName, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions, true);
    }
}