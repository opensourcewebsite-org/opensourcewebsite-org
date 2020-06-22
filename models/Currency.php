<?php

namespace app\models;

use app\models\queries\CurrencyQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "currency".
 *
 * @property int                  $id
 * @property string               $code
 * @property string               $name
 * @property string               $symbol
 * @property DebtRedistribution[] $debtRedistributions
 */
class Currency extends ActiveRecord
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

    public static function find()
    {
        return new CurrencyQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'     => 'ID',
            'code'   => 'Code',
            'name'   => 'Name',
            'symbol' => 'Symbol',
        ];
    }

    public function getDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), ['currency_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getCurrencyLabel()
    {
        return $this->code . ' - ' . $this->name;
    }
}
