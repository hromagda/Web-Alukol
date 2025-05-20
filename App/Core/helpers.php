<?php
// App/Core/helpers.php

function url(string $path = '', array $query = [], string $fragment = ''): string {
    $base = '/' . ltrim($path, '/');

    if (!empty($query)) {
        $base .= '?' . http_build_query($query);
    }

    if ($fragment !== '') {
        $base .= '#' . urlencode($fragment);
    }

    return $base;
}

function is_active(string $path = ''): string
{
    return $_SERVER['REQUEST_URI'] === url($path) ? ' active' : '';
}

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}