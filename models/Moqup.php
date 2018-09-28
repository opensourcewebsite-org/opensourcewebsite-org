<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Moqup model
 *
 *
 */
class Moqup extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'moqup';
    }

    /** 
     * {@inheritdoc} 
     */ 
    public function rules()
    {
        return [
            [['user_id', 'title', 'html'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['created_at'], 'default', 'value' => time()],
            [['html'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => Yii::t('moqup', 'ID'), 
            'user_id' => Yii::t('moqup', 'User'), 
            'title' => Yii::t('moqup', 'Title'), 
            'html' => Yii::t('moqup', 'Html'), 
            'created_at' => Yii::t('moqup', 'Created At'), 
            'updated_at' => Yii::t('moqup', 'Updated At'), 
        ]; 
    } 

    /** 
     * @return \yii\db\ActiveQuery 
     */ 
    public function getCss() 
    { 
        return $this->hasOne(Css::className(), ['moqup_id' => 'id']); 
    }

    /** 
     * @return \yii\db\ActiveQuery 
     */ 
    public function getUser() 
    { 
        return $this->hasOne(User::className(), ['id' => 'user_id']); 
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('user_moqup_follow', ['moqup_id' => 'id']);
    }

    /**
     * @return integer The number of followers to this moqup
     */
    public function getFollowersNumber() {
        return count($this->followers);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrigin()
    {
        return $this->hasOne(Moqup::className(), ['id' => 'forked_of']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForks()
    {
        return $this->hasMany(Moqup::className(), ['forked_of' => 'id']);
    }

    /**
     * @return integer The number of forks that this moqup have
     */
    public function getForksNumber() {
        return count($this->forks);
    }
}
