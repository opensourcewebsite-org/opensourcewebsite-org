<?php

namespace app\modules\bot\components\helpers;

class ExternalLink
{
    public static function getOSMLink($latitude, $longitude)
    {
        return "https://www.openstreetmap.org/#map=14/$latitude/$longitude";
    }

    public static function getBotLink()
    {
        return 'https://t.me/opensourcewebsite_bot';
    }

    public static function getGroupLink()
    {
        return 'https://t.me/opensourcewebsite';
    }

    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAccountFullLink($publicKey, $name = null)
    {
        return '<a href="https://stellar.expert/explorer/public/account/' . $publicKey . '">' . ($name ?: $publicKey) . '</a>';
    }

    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAssetFullLink($asset, $publicKey, $name = null)
    {
        return '<a href="https://stellar.expert/explorer/public/asset/' . $asset . '-' . $publicKey . '">' . ($name ?: $asset . '-' . $publicKey) . '</a>';
    }
}
