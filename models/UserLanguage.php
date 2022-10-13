<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class UserLanguage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_language}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'language_id', 'language_level_id'], 'integer'],
            [['user_id', 'language_id', 'language_level_id'], 'required'],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, [ 'id' => 'user_id' ]);
    }

    public function getLevel(): ActiveQuery
    {
        return $this->hasOne(LanguageLevel::class, [ 'id' => 'language_level_id' ]);
    }

    public function getLanguage(): ActiveQuery
    {
        return $this->hasOne(Language::class, [ 'id' => 'language_id' ]);
    }

    public function getLabel()
    {
        return $this->language->name . ' - ' . Yii::t('user', $this->level->description);
    }
}
