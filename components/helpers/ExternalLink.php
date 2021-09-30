<?php

namespace app\components\helpers;

class ExternalLink
{
    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAccountLink($publicKey)
    {
        return 'https://stellar.expert/explorer/public/account/' . $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAssetLink($asset, $publicKey)
    {
        return 'https://stellar.expert/explorer/public/asset/' . $asset . '-' . $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTelegramAccountLink($username)
    {
        return 'https://t.me/' . $username;
    }
}
