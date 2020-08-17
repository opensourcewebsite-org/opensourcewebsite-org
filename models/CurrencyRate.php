<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "currency_rate".
 *
 * @property int $id
 * @property int $from_currency_id
 * @property int $to_currency_id
 * @property float $rate
 * @property int $updated_at
 */
class CurrencyRate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_rate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_currency_id', 'to_currency_id', 'rate', 'updated_at'], 'required'],
            [['from_currency_id', 'to_currency_id', 'updated_at'], 'integer'],
            [['rate'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_currency_id' => 'From Currency ID',
            'to_currency_id' => 'To Currency ID',
            'rate' => 'Rate',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFromCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'from_currency_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getToCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'to_currency_id']);
    }
}
