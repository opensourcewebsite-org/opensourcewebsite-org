<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "css".
 *
 * @property int $id
 * @property int $moqup_id
 * @property string $css
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Moqup $moqup
 */
class Css extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'css';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['moqup_id'], 'required'],
            [['moqup_id', 'created_at', 'updated_at'], 'integer'],
            [['created_at'], 'default', 'value' => time()],
            [['css'], 'string'],
            [['moqup_id'], 'exist', 'skipOnError' => true, 'targetClass' => Moqup::className(), 'targetAttribute' => ['moqup_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('moqup', 'ID'),
            'moqup_id' => Yii::t('moqup', 'Moqup ID'),
            'css' => Yii::t('moqup', 'Css'),
            'created_at' => Yii::t('moqup', 'Created At'),
            'updated_at' => Yii::t('moqup', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoqup()
    {
        return $this->hasOne(Moqup::className(), ['id' => 'moqup_id']);
    }

    /**
     * Make some changes before the record is saved
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->updated_at = time();

        return true;
    }
}
