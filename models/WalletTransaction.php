<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wallet_transaction".
 *
 * @property int $id
 * @property int $currency_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property float $amount
 * @property int $type
 * @property int $created_at
 *
 * @property Currency $currency
 * @property User $fromUser
 * @property User $toUser
 */
class WalletTransaction extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wallet_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currency_id', 'from_user_id', 'to_user_id', 'amount', 'type', 'created_at'], 'required'],
            [['currency_id', 'from_user_id', 'to_user_id', 'type', 'created_at'], 'integer'],
            ['amount', 'double', 'min' => -9999999999999.99, 'max' => 9999999999999.99],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['to_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'currency_id' => 'Currency ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'amount' => 'Amount',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id']);
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }
}
