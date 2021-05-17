<?php
declare(strict_types=1);

namespace app\models\validators;

class UrlTrimmer {

    public function trim(string $url): string
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl['host'] ?? false) {
            $parsedUrl['host'] = trim($parsedUrl['host'], " \t\n\r\0\x0B.");
        }

        return ($parsedUrl['scheme'] ? $parsedUrl['scheme'].'://' : '') . $parsedUrl['host'] . $parsedUrl['path'] . '?' . $parsedUrl['query'];
    }
}
