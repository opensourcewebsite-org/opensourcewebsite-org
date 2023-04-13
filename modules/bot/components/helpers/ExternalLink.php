<?php

namespace app\modules\bot\components\helpers;

use app\components\helpers\Html;
use Yii;

class ExternalLink extends \app\components\helpers\ExternalLink
{
    /**
     * {@inheritdoc}
     */
    public static function getOSMLink($latitude, $longitude)
    {
        return 'https://www.openstreetmap.org/#map=14/' . $latitude . '/' . $longitude;
    }

    /**
     * {@inheritdoc}
     */
    public static function getOSMFullLink($latitude, $longitude, $name = null)
    {
        return '<a href="' . self::getOSMLink($latitude, $longitude) . '">' . ($name ?: round($latitude, 4) . ',' . round($longitude, 4)) . '</a>';
    }

    /**
     * {@inheritdoc}
     */
    public static function getBotLink()
    {
        if (($module = Yii::$app->getModule('bot')) && ($bot = $module->getBot())) {
            return 'https://t.me/' . $bot->getUsername();
        }

        return 'https://t.me/opensourcewebsite_bot';
    }

    /**
     * {@inheritdoc}
     */
    public static function getBotToAddGroupLink()
    {
        return self::getBotLink() . '?startgroup=true';
    }

    /**
     * {@inheritdoc}
     */
    public static function getBotStartLink($text, $text2 = null)
    {
        return self::getBotLink() . '?start=' . $text . ($text2 ? $text2 : '');
    }

    /**
     * {@inheritdoc}
     */
    public static function getBotGroupGuestLink($chatId)
    {
        return self::getBotLink() . '?start=' . $chatId;
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

    /**
     * {@inheritdoc}
     */
    public static function getTelegramAccountLink($username)
    {
        return 'https://t.me/' . $username;
    }
}
