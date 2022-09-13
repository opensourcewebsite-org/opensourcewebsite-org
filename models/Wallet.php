<?php

namespace app\models;

use app\models\queries\WalletQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wallet".
 *
 * @property int $currency_id
 * @property int $user_id
 * @property float $amount
 *
 * @property Currency $currency
 * @property User $user
 */
class Wallet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wallet}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currency_id', 'user_id'], 'required'],
            [['currency_id', 'user_id'], 'integer'],
            ['amount', 'default', 'value' => 0],
            ['amount', 'double', 'min' => -9999999999999.99, 'max' => 9999999999999.99],
            [['currency_id', 'user_id'], 'unique', 'targetAttribute' => ['currency_id', 'user_id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'currency_id' => 'Currency ID',
            'user_id' => 'User ID',
            'amount' => 'Amount',
        ];
    }

    public static function find(): WalletQuery
    {
        return new WalletQuery(get_called_class());
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
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

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id'])
            ->andWhere([
                'or',
                [WalletTransaction::tableName() . '.from_user_id' => $this->user_id],
                [WalletTransaction::tableName() . '.to_user_id' => $this->user_id],
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOutTransactions()
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id', 'from_user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInTransactions()
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id', 'to_user_id' => 'user_id']);
    }
}
