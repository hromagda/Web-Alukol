<?php

use voku\helper\ASCII;
// App/Core/helpers.php

/**
 * Vytvoří URL cestu s volitelnými GET parametry a fragmentem.
 *
 * @param string $path Cílová cesta (např. 'clanek/123').
 * @param array $query Pole GET parametrů (např. ['page' => 2]).
 * @param string $fragment Fragment URL (např. 'sekce1').
 * @return string Vygenerovaná URL (např. '/clanek/123?page=2#sekce1').
 */
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

/**
 * Zkontroluje, zda aktuální URL odpovídá dané cestě, a vrátí třídu CSS 'active' pro zvýraznění.
 *
 * @param string $path Cesta pro kontrolu (např. 'clanek/123').
 * @return string Řetězec ' active' pokud je aktivní, jinak prázdný řetězec.
 */
function is_active(string $path = ''): string
{
    return $_SERVER['REQUEST_URI'] === url($path) ? ' active' : '';
}

/**
 * Vrátí singleton PDO připojení k databázi podle konfigurace.
 *
 * @return PDO Instance PDO připojení.
 * @throws PDOException Pokud se připojení nezdaří.
 */
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

/**
 * Vrátí CSRF token uložený v session nebo ho vygeneruje, pokud neexistuje.
 *
 * @return string CSRF token.
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vygeneruje HTML input element pro vložení CSRF tokenu do formuláře.
 *
 * @return string HTML kód skrytého inputu s CSRF tokenem.
 */
function csrf_field()
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Ověří platnost CSRF tokenu proti hodnotě uložené v session.
 *
 * @param string|null $token Token přijatý z formuláře.
 * @return bool True pokud token odpovídá, false jinak.
 */
function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Převádí text na URL-friendly slug (např. odstraní diakritiku, mezery nahradí pomlčkami).
 *
 * @param string $text Vstupní text pro převod.
 * @return string Slug vhodný pro URL.
 */
function slugify(string $text): string
{
    $text = ASCII::to_slugify($text); // správný převod diakritiky pro slug
    return trim($text, '-');
}