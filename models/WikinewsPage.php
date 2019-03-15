<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wikinews_page".
 *
 * @property int $id
 * @property int $language_id
 * @property string $title
 * @property int $group_id
 * @property int $pageid
 * @property int $created_by
 * @property int $created_at
 * @property int $parsed_at
 * @property object $language
 */
class WikinewsPage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wikinews_page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_id', 'title','wikinews_page_url'], 'required'],
            [['title'], 'match','pattern' => '/^[a-zA-Z][a-zA-Z\s@#$%&*().,;\-\/]*$/'],
            [['title'], 'string', 'max' => 255],
            [['wikinews_page_url'], 'url'],
        ];
    }

    /**
     * Make some changes before the record is saved
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if(empty($this->id)){
            $this->created_at=time();
            $this->created_by=Yii::$app->user->identity->id;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_id' => 'Language',
            'title' => 'Title',
            'wikinews_page_url' => 'Wikinews Page Url',
            'group_id' => 'Group ID',
            'pageid' => 'Page ID',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'parsed_at' => 'Parsed at',
        ];
    }

    public function getLanguage()
    {
        return $this->hasOne(WikinewsLanguage::class, ['id' => 'language_id']);
    }
}
