<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\components\WikipediaParser;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

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
            'token' => 'Watchlist Token',
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
        return $this->hasOne(WikiLanguage::class, ['id' => 'language_id']);
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
            ],
        ];
    }

    public function afterValidate()
    {
        parent::afterValidate();

        if (!$this->hasErrors()) {
            $parser = new WikipediaParser([
                'token' => $this,
                'user_id' => Yii::$app->user->id,
                'language_id' => $this->language->id,
            ]);

            try {
                $parser->run(true);
            } catch (\yii\web\ServerErrorHttpException $e) {
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
     * @return array|null The list of wikipages id
     */
    public function getWikiPagesIds()
    {
        $currentLanguageWikiPages = WikiPage::find()
            ->select('id')
            ->where(['language_id' => $this->language_id]);

        $ids = UserWikiPage::find()
            ->select('wiki_page_id')
            ->where([
                'user_id' => $this->user_id,
                'wiki_page_id' => $currentLanguageWikiPages,
            ])
            ->all();

        return ArrayHelper::getColumn($ids, 'wiki_page_id');
    }

    public static function findByLanguage($id)
    {
        return self::findOne(['user_id' => Yii::$app->user->id, 'language_id' => $id]);
    }

    /**
     * Count the list of missing pages
     */
    public function getCountMissingPages()
    {
        $missingPages = $this->instanceMissingPages();
        return $missingPages->count();
    }


    /**
     * Creating base query to get missing user's pages
     * @return \yii\db\Query
     */
    public function instanceMissingPages()
    {

        // Getting IDs from current language
        $queryExcludeCurrent = WikiPage::find()
            ->joinWith('users')
            ->select('group_id')
            ->distinct()
            ->where(['{{%user}}.id' => $this->user_id])
            ->andWhere(['language_id' => $this->language_id]);

        // Getting all IDs excluding currents for users from all other languages
        $queryAllIds = WikiPage::find()
            ->joinWith('users')
            ->select('group_id')
            ->distinct()
            ->where(['NOT IN','group_id', $queryExcludeCurrent])
            ->andWhere(['{{%user}}.id' => $this->user_id]);

        $queryMissingPages = WikiPage::find()
            ->joinWith('users')
            ->select(['DISTINCT(`group_id`)', 'title', 'language_id'])
            ->where(['group_id' => $queryAllIds])
            ->andWhere([
                'language_id' => $this->language_id,
            ]);

        return $queryMissingPages;
    }
}
