<?php

namespace app\models;

use Yii;
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
class WikiPage extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wiki_page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_id', 'ns', 'title'], 'required'],
            [['language_id', 'ns', 'group_id', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'ns' => 'Ns',
            'title' => 'Title',
            'group_id' => 'Group ID',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_wiki_page', ['wiki_page_id' => 'id']);
    }

    public function getWikiUrl()
    {
        return "https://{$this->language->code}.wikipedia.org/wiki/{$this->urlTitle}";
    }

    public function getLanguage()
    {
        return $this->hasOne(WikiLanguage::class, ['id' => 'language_id']);
    }

    public function getUrlTitle()
    {
        return str_replace(' ', '_', $this->title);
    }

    public function getRating()
    {
        $total = 0;

        if (!empty($this->users)) {
            foreach ($this->users as $user) {
                $total += $user->rating;
            }
        }
        return $total;
    }
}
