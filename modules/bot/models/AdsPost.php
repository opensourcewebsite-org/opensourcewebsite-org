<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class AdsPost extends ActiveRecord
{
    public const STATUS_ACTIVATED = 'activated';
    public const STATUS_NOT_ACTIVATED = 'not_activated';

    public const LIVE_DAYS = 14;

    public static function tableName()
    {
        return 'ads_post';
    }

    public function rules()
    {
        return [
            [['user_id', 'title', 'description', 'currency_id', 'price', 'location_lat', 'location_lon', 'category_id', 'status', 'created_at', 'updated_at'], 'required'],
            [['title', 'description', 'location_lat', 'location_lon', 'status'], 'string'],
            [['user_id', 'currency_id', 'price', 'delivery_km', 'category_id', 'created_at', 'updated_at', 'edited_at'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public function getKeywords()
    {
        return $this->hasMany(AdKeyword::className(), ['id' => 'keyword_id'])
            ->viaTable('{{%ads_post_keyword}}', ['ads_post_id' => 'id']);
    }

    public function getPhotos()
    {
        return $this->hasMany(AdPhoto::className(), ['ads_post_id' => 'id']);
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVATED:
                return Yii::t('bot', 'Активно');
            case self::STATUS_NOT_ACTIVATED:
                return Yii::t('bot', 'Не активно');
            default:
                Yii::error('AdsPost status is invalid');
                return '';
        }
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVATED && (time() - $this->updated_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    public function getMatches()
    {
        return $this->hasMany(AdsPostSearch::className(), ['id' => 'ads_post_search_id'])
            ->viaTable('{{%ad_matches}}', ['ads_post_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);

        if (!$this->isActive()) {
            return;
        }
        
        foreach ($this->getKeywords()->all() as $adKeyword) {
            foreach (AdsPostSearchKeyword::find()->where([
                'keyword_id' => $adKeyword->id,
            ])->all() as $adsPostSearchKeyword) {
                $adsPostSearch = AdsPostSearch::findOne($adsPostSearchKeyword->ads_post_search_id);

                $adsPostSearchMatches = $this->getMatches()->where([
                    'id' => $adsPostSearch->id,
                ])->one();

                if ($adsPostSearch->matches($this) && !isset($adsPostSearchMatches) && $adsPostSearch->isActive()) {
                    $this->link('matches', $adsPostSearch);
                }
            }
        }
    }

    public function markToUpdateMatches()
    {
        if ($this->edited_at === null) {
            $this->setAttributes([
                'edited_at' => time(),
            ]);
            $this->save();
        }
    }

    public static function validatePrice($price)
    {
        return is_numeric($price) && round($price, 2) == $price && $price >= 0;
    }

    public static function validateLocation($location)
    {
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

    public static function validateRadius($radius)
    {
        return is_numeric($radius) && $radius >= 0;
    }

    private static function removeExtraChars($str)
    {
        return preg_replace('/[^\d\. ]/', '', $str);
    }

    private static function getLocationSlices($location)
    {
        $slices = explode(' ', $location);

        if (count($slices) != 2) {
            return null;
        } else {
            return $slices;
        }
    }
}
