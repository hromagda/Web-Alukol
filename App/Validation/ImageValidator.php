<?php

namespace App\Validation;

class ImageValidator
{
    /**
     * Povolené MIME typy.
     */
    public static array $defaultAllowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    /**
     * Ověří MIME typ obrázku podle skutečného obsahu.
     */
    public static function isValidMimeType(string $filePath, array $allowedTypes = null): bool
    {
        $allowedTypes = $allowedTypes ?? self::$defaultAllowedTypes;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        return in_array($mimeType, $allowedTypes, true);
    }

    /**
     * Ověří, že velikost souboru není větší než limit.
     */
    public static function isBelowMaxSize(string $filePath, int $maxBytes = 5_000_000): bool
    {
        return filesize($filePath) <= $maxBytes;
    }

    /**
     * Ověří rozměry obrázku (např. minimální nebo maximální šířka/výška).
     */
    public static function isWithinDimensions(string $filePath, int $maxWidth = 3000, int $maxHeight = 2000): bool
    {
        [$width, $height] = getimagesize($filePath) ?: [0, 0];

        return $width <= $maxWidth && $height <= $maxHeight;
    }

    /**
     * Ověří příponu souboru.
     */
    public static function hasValidExtension(string $fileName, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions, true);
    }
}