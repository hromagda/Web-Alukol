<?php
namespace App\Core;

class MailView
{
    public static function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../../views/emails/' . $template . '.phtml';
        return ob_get_clean();
    }
}
