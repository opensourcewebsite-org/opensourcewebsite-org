<?php

namespace app\models;

use app\models\queries\WalletQuery;
use Yii;
use yii\db\ActiveQuery;
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
    public static function tableName(): string
    {
        return '{{%wallet}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['currency_id', 'user_id'], 'required'],
            [['currency_id', 'user_id'], 'integer'],
            ['amount', 'default', 'value' => 0],
            ['amount', 'double', 'min' => 0, 'max' => 9999999999999.99],
            [['currency_id', 'user_id'], 'unique', 'targetAttribute' => ['currency_id', 'user_id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
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

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id'])
            ->andWhere([
                'or',
                [WalletTransaction::tableName() . '.from_user_id' => $this->user_id],
                [WalletTransaction::tableName() . '.to_user_id' => $this->user_id],
            ]);
    }

    public function getOutTransactions(): ActiveQuery
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id', 'from_user_id' => 'user_id']);
    }

    public function getInTransactions(): ActiveQuery
    {
        return $this->hasMany(WalletTransaction::class, ['currency_id' => 'currency_id', 'to_user_id' => 'user_id']);
    }
}
