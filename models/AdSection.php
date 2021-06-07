<?php

namespace app\models;

use Yii;

class AdSection
{
    public const BUY_SELL = 1;
    public const RENT = 2;
    public const SERVICES = 3;

    public static array $adOfferNames = [
        self::BUY_SELL => 'Sell',
        self::RENT => 'Rent',
        self::SERVICES => 'Services'
    ];

    public static array $adSearchNames = [
        self::BUY_SELL => 'Buy',
        self::RENT => 'Rent',
        self::SERVICES => 'Services',
    ];

    public static function getAdOfferName(int $section): string
    {

        return Yii::t('bot', static::$adOfferNames[$section]);
    }

    public static function getAdSearchName(int $section): string
    {
        return Yii::t('bot', static::$adSearchNames[$section]);
    }

    public static function getAdOfferNames(): array
    {
        $ret = [];
        foreach (static::$adOfferNames as $key => $name) {
            $ret[$key] = Yii::t('app', $name);
        }
        return $ret;
    }

    public static function getAdSearchNames(): array
    {
        $ret = [];
        foreach (static::$adSearchNames as $key => $name) {
            $ret[$key] = Yii::t('app', $name);
        }
        return $ret;
    }
}
