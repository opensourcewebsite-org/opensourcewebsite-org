<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_wiki_page".
 *
 * @property integer $user_id
 * @property integer $wiki_page_id
 * 
 * @property User $user
 * @property WikiPage $wikiPage
 */
class UserWikiPage extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_wiki_page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'wiki_page_id'], 'required'],
            [['user_id', 'wiki_page_id'], 'integer'],
            [
                ['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
            [
                ['wiki_page_id'], 'exist', 'skipOnError' => true, 'targetClass' => WikiPage::class,
                'targetAttribute' => ['wiki_page_id' => 'id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'wiki_page_id' => Yii::t('app', 'Wiki Page ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getWikiPage()
    {
        return $this->hasOne(WikiPage::class, ['id' => 'wiki_page_id']);
    }
}
