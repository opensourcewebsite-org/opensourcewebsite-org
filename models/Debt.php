<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "debt".
 *
 * @property int $id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property int $currency_id
 * @property int $amount
 * @property int $status
 * @property string $valid_from_date
 * @property string $valid_from_time
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class Debt extends ActiveRecord
{

    const STATUS_PENDING = 0;
    const STATUS_CONFIRM = 1;
    const DIRECTION_DEPOSIT = 1;
    const DIRECTION_CREDIT = 2;
    const SCENARIO_STATUS_CONFIRM = 'status-confirm';

    public $user;
    public $direction;
    public $deposit;
    public $credit;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currency_id', 'amount'], 'required'],
            [['user', 'direction'], 'required', 'on' => 'default'],
            [['from_user_id', 'to_user_id', 'currency_id', 'amount', 'status'], 'integer'],
            [['valid_from_date', 'valid_from_time', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_user_id' => 'From User',
            'to_user_id' => 'User',
            'currency_id' => 'Currency',
            'amount' => 'Amount',
            'status' => 'Status',
            'valid_from_date' => 'Valid From Date',
            'valid_from_time' => 'Valid From Time',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_STATUS_CONFIRM] = [];
        return $scenarios;
    }

    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    public function getUserDisplayName($direction)
    {
        $name = $this->fromUser->name;
        if (!empty($this->fromUser->contact)) {
            $name = $this->fromUser->getDisplayName();
        }
        if ((int) $direction === self::DIRECTION_CREDIT) {
            $name = $this->toUser->name;
            if (!empty($this->toUser->contact)) {
                $name = $this->toUser->getDisplayName();
            }
        }

        return $name;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->from_user_id = Yii::$app->user->id;
            $this->to_user_id = $this->user;

            if ((int) $this->direction === self::DIRECTION_DEPOSIT) {
                $this->from_user_id = $this->user;
                $this->to_user_id = Yii::$app->user->id;
            }

            if (!empty($this->valid_from_date)) {
                $validFromDate = \DateTime::createFromFormat('m/d/yy', $this->valid_from_date);
                $this->valid_from_date = $validFromDate->format('Y-m-d');
            }
        }

        return parent::beforeSave($insert);
    }
}
