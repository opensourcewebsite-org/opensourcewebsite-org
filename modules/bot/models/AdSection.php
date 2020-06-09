<?php

namespace app\modules\bot\models;

use Yii;

class AdSection
{
    public const BUY_SELL = 1;
    public const RENT = 2;
    public const SERVICES = 3;

    private static $placeNames = [
        self::BUY_SELL => Yii::t('bot', 'Sell'),
        self::RENT => Yii::t('bot', 'Rent'),
        self::SERVICES => Yii::t('bot_ad', 'Services'),
    ];

    private static $findNames = [
        self::BUY_SELL => Yii::t('bot', 'Buy'),
        self::RENT => Yii::t('bot', 'Rent'),
        self::SERVICES => Yii::t('bot_ad', 'Services'),
    ];

    public static function getAdOfferName($section)
    {
        return self::$placeNames[$section];
    }

    public static function getAdSearchName($section)
    {
        return self::$findNames[$section];
    }
}
