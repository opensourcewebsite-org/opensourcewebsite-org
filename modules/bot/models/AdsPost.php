<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

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
            [['title', 'description', 'photo_file_id', 'location_lat', 'location_lon', 'status'], 'string'],
            [['user_id', 'currency_id', 'price', 'delivery_km', 'category_id', 'created_at', 'updated_at'], 'integer'],
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
}
