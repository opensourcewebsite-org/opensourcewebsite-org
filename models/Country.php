<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "country".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $slug
 * @property string $wikipedia
 */
class Country extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code', 'slug'], 'required'],
            [['name', 'code', 'slug', 'wikipedia'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'slug' => 'Slug',
            'wikipedia' => 'Wikipedia',
        ];
    }
}
