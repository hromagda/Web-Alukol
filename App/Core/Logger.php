<?php
namespace App\Core;

/**
 * Jednoduchý logger pro ukládání chybových zpráv do log souboru.
 */
class Logger
{
    /**
     * Zapíše chybovou zprávu do log souboru s časovou značkou a volitelným kontextem.
     *
     * @param string $message Text chybové zprávy.
     * @param array $context Pole dodatečných dat pro logování (např. výjimky, stav apod.).
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] ERROR: $message $contextJson\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
