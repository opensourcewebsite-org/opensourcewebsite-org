<?php

namespace yii\helpers;

use Yii;

class Html extends BaseHtml
{
    public static function a($text, $url = null, $options = [])
    {
        if ($url !== null) {
            $parsedUrl = Url::to($url);
            $options['href'] = $parsedUrl;
            if (static::isUrlExternal($parsedUrl)) {
                $options = array_merge([
                    'target' => '_blank',
                    'rel' => 'nofollow noreferrer noopener',
                ], $options);
            }
        }

        return static::tag('a', $text, $options);
    }

    /**
     * Check if url is external
     *
     * @param $url
     *
     * @return bool
     */
    protected static function isUrlExternal($url)
    {

        $host = Yii::$app->params['host'] ?? null;

        $components = parse_url($url);
        //if url parsing failed
        if ($components === false) {
            return false;
        }
        //if relative url
        if (empty($components['host'])) {
            return false;
        }
        $urlHost = $components['host'];
        //if url not contains domain of site
        if (strcasecmp($urlHost, $host) === 0) {
            return false;
        }

        return strripos($urlHost, '.' . $host) !== strlen($urlHost) - strlen('.' . $host);
    }
}
