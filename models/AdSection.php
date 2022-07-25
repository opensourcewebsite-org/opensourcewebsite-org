<?php

namespace app\models;

use Yii;

class AdSection
{
    public const BUY_SELL = 1;
    public const RENT = 2;
    public const SERVICES = 3;

    public static function getAdOfferName(int $section): string
    {
        return static::getAdOfferNames()[$section];
    }

    public static function getAdSearchName(int $section): string
    {
        return static::getAdSearchNames()[$section];
    }

    public static function getAdOfferNames(): array
    {
        return [
            self::BUY_SELL => Yii::t('ad_offer', 'Sell'),
            self::RENT => Yii::t('ad_offer', 'Rent'),
            self::SERVICES => Yii::t('ad_offer', 'Services')
        ];
    }

    public static function getAdSearchNames(): array
    {
        return [
            self::BUY_SELL => Yii::t('ad_search', 'Buy'),
            self::RENT => Yii::t('ad_search', 'Rent'),
            self::SERVICES => Yii::t('ad_search', 'Services'),
        ];
    }
}
