<?php

namespace app\modules\apiTesting\models;

use app\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_test_project".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name Project name
 * @property string|null $description Description (optional)
 * @property int $project_type Project Type
 * @property int $created_at
 * @property int|null $updated_at
 *
 * @property User $user
 */
class ApiTestProject extends \yii\db\ActiveRecord
{
    const PROJECT_TYPE_PRIVATE = 0;
    const PROJECT_TYPE_PUBLIC = 1;

    public static function projectTypes()
    {
        return [
            self::PROJECT_TYPE_PUBLIC => 'Public Project',
            self::PROJECT_TYPE_PRIVATE => 'Private project'

        ];
    }

    public static function projecTypesDesription()
    {
        return [
            self::PROJECT_TYPE_PRIVATE => 'People can only join if they added',
            self::PROJECT_TYPE_PUBLIC => 'Anyone can find the project in search and join',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'project_type'], 'required'],
            [['user_id', 'project_type', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'name' => 'Project name',
            'description' => 'Description (optional)',
            'project_type' => 'Project Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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

    public function getServers()
    {
        return $this->hasMany(ApiTestServer::className(), ['project_id' => 'id']);
    }

    public function getLabels()
    {
        return $this->hasMany(ApiTestLabel::className(), ['project_id' => 'id']);
    }

    public function getRequests()
    {
        return $this->hasMany(ApiTestRequest::className(), ['server_id' => 'id'])->viaTable(ApiTestServer::tableName(), ['project_id' => 'id']);
    }

    public function getTeams()
    {
        return $this->hasOne(ApiTestTeam::className(), ['project_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestProjectQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestProjectQuery(get_called_class());
    }
}
