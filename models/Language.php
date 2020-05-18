<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $name_ascii
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'name_ascii'], 'required'],
            [['code', 'name', 'name_ascii'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('common', 'Name'),
            'name_ascii' => Yii::t('app', 'Name Ascii'),
        ];
    }
}
