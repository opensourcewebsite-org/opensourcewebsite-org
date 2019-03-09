<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "wiki_page".
 *
 * @property int $id
 * @property int $language_id
 * @property int $ns
 * @property string $title
 * @property int $group_id
 * @property int $updated_at
 */
class WikiNews extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wiki_news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'link'], 'required'],
            [['title'], 'string'],
            [['link'], 'string', 'max' => 2083],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'link' => 'Link',
        ];
    }
}
