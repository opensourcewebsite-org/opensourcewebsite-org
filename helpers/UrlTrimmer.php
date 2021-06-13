<?php
declare(strict_types=1);

namespace app\helpers;

class UrlTrimmer
{
    public function trim(string $url): string
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl['host'] ?? false) {
            $parsedUrl['host'] = trim($parsedUrl['host'], " \t\n\r\0\x0B.");
        }

        return (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '')
            . ( $parsedUrl['host'] ?? '')
            . ( $parsedUrl['path'] ?? '')
            . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
    }
}
