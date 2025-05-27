<?php
namespace App\Core;

/**
 * Pomocná třída pro renderování emailových šablon.
 */
class MailView
{
    /**
     * Načte a vykreslí emailovou šablonu s předanými daty.
     *
     * @param string $template Název šablony (soubor bez přípony .phtml) ve složce views/emails.
     * @param array $data Asociativní pole dat, která budou extrahována do proměnných dostupných v šabloně.
     * @return string Vygenerovaný HTML obsah emailu.
     */
    public static function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../../views/emails/' . $template . '.phtml';
        return ob_get_clean();
    }
}
