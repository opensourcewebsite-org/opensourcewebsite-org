<?php

namespace app\modules\apiTesting\models;

use app\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_test_team".
 *
 * @property int $user_id User identity
 * @property int $project_id Project identity
 * @property int $status
 * @property int|null $invited_at
 * @property int $invited_by
 * @property ApiTestProject $project
 * @property User $user
 */
class ApiTestTeam extends \yii\db\ActiveRecord
{
    const STATUS_INVITED = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 3;

    public static function statusLabels()
    {
        return [
            self::STATUS_INVITED => 'Invited',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_ACCEPTED => 'Accepted'
        ];
    }

    public function getStatusLabel()
    {
        return self::statusLabels()[$this->status];
    }

    public function getIsCurrentUser()
    {
        return $this->user_id == \Yii::$app->user->id;
    }

    public function getIsOwner()
    {
        return $this->project->user_id == $this->user_id;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'invited_at',
                'updatedAtAttribute' => false,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_team';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'status', 'invited_by'], 'required'],
            [['user_id', 'project_id', 'invited_at', 'status', 'invited_by'], 'integer'],
            [['user_id', 'project_id'], 'unique', 'targetAttribute' => ['user_id', 'project_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestProject::className(), 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User',
            'project_id' => 'Project identity',
            'invited_at' => 'Invited At',
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery|ApiTestProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(ApiTestProject::className(), ['id' => 'project_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestTeamQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestTeamQuery(get_called_class());
    }
}
