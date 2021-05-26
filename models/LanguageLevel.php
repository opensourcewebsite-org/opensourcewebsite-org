<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

/**
 * Class LanguageLevel
 * @package app\models
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
    public static function tableName(): string
    {
        return '{{%language_level}}';
    }

    public function rules(): array
    {
        return [
            [ [ 'code', 'description' ], 'string' ],
            [ [ 'value' ], 'integer' ],
            [ [ 'description', 'value' ], 'required' ],
        ];
    }

    public function getLabel(): string
    {
        return (isset($this->code) ? $this->code . ' - ' : '') . Yii::t('app', $this->description);
    }
}
