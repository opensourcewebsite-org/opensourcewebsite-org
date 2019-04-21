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
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['language_id', 'group_id', 'pageid', 'created_by', 'created_at', 'parsed_at'], 'integer'],
        ];
    }
	
	/**
     * Validates the url.
     * This method serves as the inline validation for url.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
	public function validateUrl($attribute)
	{
		$valid = true;
		$attr = $this->$attribute;
		$validateUrl = preg_match("/^https:\/\/([a-z]{2}).wikinews.org\/wiki\/([A-Za-zА-Яа-я0-9%,_.-]+)/ui", $attr, $matches);
        if (!$validateUrl) {
			$valid = false;
		}
		elseif ($matches[1]) {
			$langCode = $matches[1];
			$langValid = WikinewsLanguage::find()->where(['code'=>$langCode])->count();
			if(!$langValid) {
				$valid = false;
			}
		}
		else {
			$valid = false;
		}
		if (!$valid) {
			$this->addError($attribute, 'Url is not valid.');
		}
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
