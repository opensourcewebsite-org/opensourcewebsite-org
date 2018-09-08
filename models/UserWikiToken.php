<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\components\WikiParser;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_wiki_token".
 *
 * @property int $id
 * @property int $user_id
 * @property int $language_id
 * @property string $token
 * @property string $wiki_username
 * @property int $status
 * @property int $updated_at
 */
class UserWikiToken extends ActiveRecord
{

    const STATUS_OK = 0;
    const STATUS_HAS_ERROR = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_wiki_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'language_id', 'token', 'wiki_username'], 'required'],
            [['user_id', 'language_id', 'status', 'updated_at'], 'integer'],
            [['token', 'wiki_username'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'language_id' => 'Language ID',
            'token' => 'Token',
            'wiki_username' => 'Wiki Username',
            'status' => 'Status',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    public function afterValidate()
    {
        parent::afterValidate();

        if (!$this->hasErrors()) {
            $parser = new WikiParser([
                'token' => $this,
                'user_id' => Yii::$app->user->id,
                'language_id' => $this->language->id,
            ]);

            try {
                $parser->run();
            } catch (ServerErrorHttpException $e) {
                $this->addError('token', $e->getMessage());
            }
        }
    }

    public function getName()
    {
        return "{$this->language->name} ({$this->language->code}.wikipedia.org)";
    }

    public function getAllPagesRatingCount()
    {
        return WikiPage::find()
            ->select(['{{%wiki_page}}.*', 'SUM({{%user}}.rating) AS rating'])
            ->joinWith('users')
            ->andWhere(['{{%wiki_page}}.language_id' => $this->language_id])
            ->groupBy('{{%wiki_page}}.id')
            ->having(['>', 'rating', 0])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWikiPagesIds()
    {
        $currentLanguageWikiPages = WikiPage::find()
            ->select('id')
            ->where(['language_id' => $this->language_id]);

        return UserWikiPage::find()
                ->select('wiki_page_id')
                ->where([
                    'user_id' => $this->user_id,
                    'wiki_page_id' => $currentLanguageWikiPages,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMissingWikiPagesForUser()
    {
        $allUserWikiPages = UserWikiPage::find()
            ->select('wiki_page_id')
            ->where([
            'user_id' => $this->user_id,
        ]);
        $userWikiPagesGroups = WikiPage::find()
            ->select('group_id')
            ->distinct()
            ->where([
            '{{%wiki_page}}.id' => $allUserWikiPages,
        ]);

        return WikiPage::find()
                ->where(['not in', '{{%wiki_page}}.id', $allUserWikiPages])
                ->andWhere([
                    'language_id' => $this->language_id,
                    'group_id' => $userWikiPagesGroups,
        ]);
    }

    public static function findByLanguage($id)
    {
        return self::findOne(['user_id' => Yii::$app->user->id, 'language_id' => $id]);
    }
}
