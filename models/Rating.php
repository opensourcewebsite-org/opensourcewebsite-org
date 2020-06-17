<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "rating".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $type
 * @property int $created_at
 *
 * @property User $user
 */
class Rating extends \yii\db\ActiveRecord
{
    const CONFIRM_EMAIL = 0;
    const TEAM = 1;
    const DONATE = 2;
    const USE_TELEGRAM_BOT = 3;

    public static $types = [
        0 => 'Registration',
        1 => 'Contribution',
        2 => 'Donation',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rating';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'type', 'created_at'], 'integer'],
            [['amount', 'type'], 'required'],
            [['created_at'], 'default', 'value' => time()],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'amount' => Yii::t('app', 'Amount'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return integer The total rating in rating table
     */
    public static function getTotalRating()
    {
        $result = static::find()->sum('amount');

        return $result ?: 0;
    }

    public function getTypeName()
    {
        return self::$types[$this->type];
    }
}
