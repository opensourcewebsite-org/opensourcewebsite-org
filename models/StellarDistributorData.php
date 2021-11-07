<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "stellar_distributor".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class StellarDistributorData extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stellar_distributor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['updated_at'], 'integer'],
            [['key', 'value'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    public static function getNextPaymentDate(): ?string
    {
        return self::findOne(['key' => 'next_payment_date'])->value ?? null;
    }

    public static function setNextPaymentDate(string $value)
    {
        $model = self::findOne(['key' => 'next_payment_date']) ?? new self(['key' => 'next_payment_date']);

        $model->value = $value;
        $model->save();
    }
}
