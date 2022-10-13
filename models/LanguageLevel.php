<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "language_level".
 *
 * @property int $id
 * @property int $value
 * @property string $code
 * @property string $description
 *
 * @property string $label
 */
class LanguageLevel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%language_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['code', 'description'], 'string'],
            [['value' ], 'integer'],
            [['description', 'value'], 'required'],
        ];
    }

    public function getLabel(): string
    {
        return (isset($this->code) ? $this->code . ' - ' : '') . Yii::t('user', $this->description);
    }
}
