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
 * @property int $source_created_at
 * @property int $source_updated_at
 * @property int $created_by
 * @property int $created_at
 * @property int $parsed_at
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
            [['language_id', 'title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['language_id', 'group_id', 'pageid', 'source_created_at', 'source_updated_at', 'created_by',
                'created_at', 'parsed_at'], 'integer'],
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
            'source_created_at' => 'Source created at',
            'source_updated_at' => 'Source updated at',
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
