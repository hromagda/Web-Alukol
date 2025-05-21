<?php
namespace App\Core;

class Logger
{
    public static function error(string $message, array $context = []): void
    {
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] ERROR: $message $contextJson\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
