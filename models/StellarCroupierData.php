<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "stellar_croupier".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class StellarCroupierData extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%stellar_croupier}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['key'], 'required'],
            [['key', 'value'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    public static function getLastPagingToken(): ?string
    {
        return self::findOne(['key' => 'last_paging_token'])->value ?? null;
    }

    public static function setLastPagingToken(string $value)
    {
        $model = StellarCroupierData::findOne(['key' => 'last_paging_token']) ?? new StellarCroupierData(['key' => 'last_paging_token']);

        $model->value = $value;
        $model->save();
    }
}
