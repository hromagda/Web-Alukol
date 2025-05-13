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