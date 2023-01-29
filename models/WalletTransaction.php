<?php

namespace app\models;

use app\modules\bot\models\ChatTipWalletTransaction;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wallet_transaction".
 *
 * @property int $id
 * @property int $currency_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property float $amount
 * @property float $fee
 * @property int $type
 * @property int $anonymity
 * @property int $created_at
 *
 * @property Currency $currency
 * @property User $fromUser
 * @property User $toUser
 */
class WalletTransaction extends ActiveRecord
{
    // fee for internal transactions in source currency
    public const FEE = 0.01;
    public const MIN_AMOUNT = 0.01;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wallet_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['currency_id', 'from_user_id', 'to_user_id', 'amount', 'type', 'anonymity'], 'required'],
            [['currency_id', 'from_user_id', 'to_user_id', 'type', 'anonymity', 'created_at'], 'integer'],
            ['amount', 'double', 'min' => 0, 'max' => 9999999999999.99],
            ['fee', 'double', 'min' => 0, 'max' => 9999999999999.99],
            ['fee', 'default', 'value' => 0.01],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['to_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'currency_id' => 'Currency ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'amount' => 'Amount',
            'fee' => 'Fee',
            'type' => 'Type',
            'anonymity' => 'Anonymity',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getFromUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id']);
    }

    public function getToUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }

    public function getChatTipWalletTransaction(): ActiveQuery
    {
        return $this->hasOne(ChatTipWalletTransaction::class, ['transaction_id' => 'id']);
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getToUserId()
    {
        return $this->to_user_id;
    }

    public function getFromUserId()
    {
        return $this->from_user_id;
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAnonymity()
    {
        return $this->anonymity;
    }
}
