<?php

namespace app\models;

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
            [['title'], 'string', 'max' => 255],
            [['language_id', 'group_id', 'pageid', 'created_by', 'created_at', 'parsed_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_id' => 'Language ID',
            'title' => 'Title',
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
