<?php

namespace app\modules\comment\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\Issue;
use app\models\User;

/**
 * This is the model class for table "issue_comment".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $count
 * @property string $message
 * @property int $issue_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Issue $issue
 * @property User $user
 */
class IssueComment extends \yii\db\ActiveRecord
{
    public $count;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'issue_comment';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','parent_id', 'issue_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            ['parent_id', 'checkLevel'],
            [['message', 'issue_id', 'user_id'], 'required'],
            [['message'], 'string'],
            [['message'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['issue_id'], 'exist', 'skipOnError'     => true, 'targetClass' => Issue::className(),
                                    'targetAttribute' => ['issue_id' => 'id'],
            ],
            [['message'], 'string', 'max' => 500],
            [['user_id'], 'exist', 'skipOnError'     => true, 'targetClass' => User::className(),
                                   'targetAttribute' => ['user_id' => 'id'],
            ],
        ];
    }

    /**
     * @param string $attribute
     *
     * @return void
     */
    public function checkLevel($attribute)
    {
        $model = static::findOne(['id' => $this->parent_id, 'parent_id' => null]);

        if (!$model) {
            $this->addError($attribute, 'Error! Message level max 2');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'parent_id'  => 'Parent ID',
            'message'    => 'Message',
            'issue_id'   => 'Issue ID',
            'user_id'    => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIssue()
    {
        return $this->hasOne(Issue::class, ['id' => 'issue_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param $user
     *
     * @return string
     */
    public static function showUserName($user)
    {
        if (!empty($user['name'])) {
            return $user['name'];
        }

        return 'Member ' . $user['id'];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user_id = Yii::$app->user->id;

            return true;
        }

        return false;
    }
}
