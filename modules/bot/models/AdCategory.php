<?php

namespace app\modules\bot\models;

use Yii;

class AdCategory
{
    public const BUY_SELL_ID = 1;
    public const RENT_ID = 2;
    public const SERVICES_ID = 3;

    public static function getPlaceName($categoryId)
    {
        switch ($categoryId) {
            case self::BUY_SELL_ID:
                return Yii::t('bot', 'Sell');
            case self::RENT_ID:
                return Yii::t('bot', 'Rent');
            case self::SERVICES_ID:
                return Yii::t('bot', 'Services');
            default:
                Yii::warning('No ID for categoryId: ' . $categoryId);
                return '';
        }
    }

    public static function getFindName($categoryId)
    {
        switch ($categoryId) {
            case self::BUY_SELL_ID:
                return Yii::t('bot', 'Buy');
            case self::RENT_ID:
                return Yii::t('bot', 'Rent');
            case self::SERVICES_ID:
                return Yii::t('bot', 'Services');
            default:
                Yii::warning('No ID for categoryId: ' . $categoryId);
                return '';
        }
    }
}
