<?php

namespace app\models;

use Yii;

class AdSection
{
    public const BUY_SELL = 1;
    public const RENT = 2;
    public const SERVICES = 3;

    public static function getAdOfferName($section)
    {
        $adOfferNames = [
            1 => Yii::t('bot', 'Sell'),
            2 => Yii::t('bot', 'Rent'),
            3 => Yii::t('bot_ad', 'Services'),
        ];

        return $adOfferNames[$section];
    }

    public static function getAdSearchName($section)
    {
        $adSearchNames = [
            1 => Yii::t('bot', 'Buy'),
            2 => Yii::t('bot', 'Rent'),
            3 => Yii::t('bot_ad', 'Services'),
        ];

        return $adSearchNames[$section];
    }
}
