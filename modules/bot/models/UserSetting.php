<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class UserSetting extends ActiveRecord
{
    public const PLACE_AD_CATEGORY_ID = 'place_ad_category_id';
    public const PLACE_AD_TITLE = 'place_ad_title';
    public const PLACE_AD_DESCRIPTION = 'place_ad_description';
    public const PLACE_AD_PHOTO_FILE_ID = 'place_ad_photo_file_id';
    public const PLACE_AD_CURRENCY_ID = 'place_ad_currency_id';
    public const PLACE_AD_PRICE = 'place_ad_price';
    public const PLACE_AD_LOCATION_LAT = 'place_ad_location_lat';
    public const PLACE_AD_LOCATION_LON = 'place_ad_location_lon';
    public const PLACE_AD_RADIUS = 'place_ad_radius';

    public const FIND_AD_CATEGORY_ID = 'find_ad_category_id';
    public const FIND_AD_LOCATION_LATITUDE = 'find_ad_location_latitude';
    public const FIND_AD_LOCATION_LONGITUDE = 'find_ad_location_longitude';
    public const FIND_AD_RADIUS = 'find_ad_radius';

    public const NO_DESCRIPTION = '-';
    public const NO_PHOTO_FILE_ID = '';

    public static function tableName()
    {
        return 'bot_user_setting';
    }

    public function rules()
    {
        return [
            [['user_id', 'setting'], 'required'],
            [['user_id'], 'integer'],
            [['setting', 'value'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public static function validateRadius($radius)
    {
        return is_numeric($radius) && $radius >= 0;
    }

    public static function validatePrice($price)
    {
        return is_numeric($price) && round($price, 2) == $price && $price >= 0;
    }

    private static function removeExtraChars($str)
    {
        return preg_replace('/[^\d\. ]/', '', $str);
    }

    private static function getLocationSlices($location)
    {
        $slices = explode(" ", $location);

        if (count($slices) != 2) {
            return null;
        } else {
            return $slices;
        }
    }

    public static function validateLocation($location)
    {
        Yii::warning(self::removeExtraChars($location));

        $slices = self::getLocationSlices(self::removeExtraChars($location));

        if (!isset($slices)) {
            return false;
        }

        $latitude = $slices[0];
        $longitude = $slices[1];

        return is_numeric($latitude) && is_numeric($longitude)
            && doubleval($latitude) >= -90 && doubleval($latitude) <= 90
            && doubleval($longitude) >= -180 && doubleval($longitude) <= 180;
    }

    public static function getLatitudeFromText($location)
    {
        $slices = self::getLocationSlices(self::removeExtraChars($location));

        return isset($slices) ? $slices[0] : null;
    }

    public static function getLongitudeFromText($location)
    {
        $slices = self::getLocationSlices(self::removeExtraChars($location));

        return isset($slices) ? $slices[1] : null;
    }
}
