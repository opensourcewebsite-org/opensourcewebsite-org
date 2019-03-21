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
            [['language_id', 'title'], 'required'],
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
	

    /**
     * @return the records which have "pageid"
     */
	 
    public function getWikiNews()
	{
        $wikiNews = (new \yii\db\Query())
            ->select(['n.id','l.name','n.title'])			
            ->from(WikinewsPage::tableName(). " AS n")
            ->join('LEFT JOIN', WikinewsLanguage::tableName() . " AS l", 'n.language_id = l.id')			
			->where(["OR",['!=','n.pageid',''],['IS NOT','n.pageid',NULL]])
			->orderBy('n.id')
			->all();
		return $wikiNews;
	}	

    /**
     * @return the count of total records which have "pageid"
     */
	 
    public function getWikiNewsCount()
	{
        $wikiNewsCount = (new \yii\db\Query())	
            ->from(WikinewsPage::tableName(). " AS n")
            ->join('LEFT JOIN', WikinewsLanguage::tableName() . " AS l", 'n.language_id = l.id')			
			->where(["OR",['!=','n.pageid',''],['IS NOT','n.pageid',NULL]])
			->count();

		return $wikiNewsCount;
	}
}
