<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "payment_method".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 */
class PaymentMethod extends ActiveRecord
{
    const TYPE_EMONEY = 0;
    const TYPE_BANK = 1;

    public static $types = [
        self::TYPE_EMONEY => 'E-Money',
        self::TYPE_BANK => 'Bank',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'payment_method';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
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
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
        ];
    }

    public function getCurrencies()
    {
        return $this->hasMany(Currency::class, ['id' => 'currency_id'])
                ->viaTable('payment_method_currency', ['payment_method_id' => 'id']);
    }

    public function getTypeName(): string
    {
        return self::$types[$this->type];
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->name;
    }
}
