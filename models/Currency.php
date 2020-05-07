<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "currency".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string', 'max' => 255],
            [['symbol'], 'string', 'max' => 4],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'symbol' => 'Symbol',
        ];
    }

    public function getCodeById($id)
    {
        $query = $this->findOne(['id' => $id]);
        return $query['code'];
    }

    public function getPaymentMethods()
    {
        return $this->hasMany(PaymentMethod::className(), ['id' => 'payment_method_id'])
                ->viaTable('payment_method_currency', ['currency_id' => 'id']);
    }

    public function getDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), ['currency_id' => 'id']);
    }
}