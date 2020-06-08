<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_method".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 */
class PaymentMethod extends \yii\db\ActiveRecord
{
    const TYPE_EMONEY = 0;
    const TYPE_BANK = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_method';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
        ];
    }

    public function getCurrency()
    {
        return $this->hasMany(Currency::className(), ['id' => 'currency_id'])
                ->viaTable('payment_method_currency', ['payment_method_id' => 'id']);
    }
}
