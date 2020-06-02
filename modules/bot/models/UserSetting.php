<?php
namespace app\modules\bot\models;

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

    public static function validatePrice($price) {
        return is_numeric($price) && round($price, 2) == $price && $price >= 0;
    }
}
