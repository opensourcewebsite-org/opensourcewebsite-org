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
 *
 * @property User $toUser
 * @property User $fromUser
 * @property Currency $currency
 */
class Debt extends ActiveRecord
{
    public const STATUS_PENDING = 0;
    public const STATUS_CONFIRM = 1;

    public const DIRECTION_DEPOSIT = 1;
    public const DIRECTION_CREDIT  = 2;

    public const SCENARIO_FORM = 'form';

    public $user;
    public $direction;
    public $depositPending;
    public $creditPending;
    public $depositConfirmed;
    public $creditConfirmed;

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
            //TODO [ref] These fields ('user', 'direction', and other public fields in this class)
            //       we need only on frontend form.
            //       For this purpose you should create DebtForm model with all these fields and their rules.
            //       Why: in all other places, except page /debt/create, we DON'T need these rules and fields
            //            (e.g. in console app)
            [['user', 'direction'], 'required', 'on' => self::SCENARIO_FORM],
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

    public function canConfirmDebt($direction)
    {
        $canConfirmDebt = $this->isStatusPending() && ((int) $this->created_by !== (int) $this->from_user_id);
        if ((int) $direction === self::DIRECTION_DEPOSIT) {
            $canConfirmDebt = $this->isStatusPending() && ((int) $this->created_by !== (int) $this->to_user_id);
        }

        return $canConfirmDebt;
    }

    public function canCancelDebt()
    {
        return ($this->isStatusPending() && (((int) $this->from_user_id === Yii::$app->user->id) || ((int) $this->to_user_id === Yii::$app->user->id)));
    }

    public function isStatusPending()
    {
        return (int)$this->status === Debt::STATUS_PENDING;
    }

    public function setUsersFromContact($contactUserId, $contactLinkedUserId)
    {
        if ($this->isStatusPending()) {
            $this->from_user_id = $contactLinkedUserId;
            $this->to_user_id   = $contactUserId;
        } else {
            $this->from_user_id = $contactUserId;
            $this->to_user_id   = $contactLinkedUserId;
        }
    }

    public function beforeSave($insert)
    {
        if ($this->scenario === self::SCENARIO_FORM) {
            $this->setUsersFromContact(Yii::$app->user->id, $this->user);

            if (!empty($this->valid_from_date)) {
                $validFromDate = \DateTime::createFromFormat('m/d/yy', $this->valid_from_date);
                $this->valid_from_date = $validFromDate->format('Y-m-d');
            }
        }

        return parent::beforeSave($insert);
    }

    public static function mapStatus()
    {
        return [self::STATUS_PENDING, self::STATUS_CONFIRM];
    }
}
