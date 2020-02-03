<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "groupusers".
 *
 * @property int $id
 * @property string $username
 * @property int $flag
 * @property int $chat_id
 */
class Groupuser extends ActiveRecord
{
    
    public $userIdOrName;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groupusers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'flag' => 'Flag',
            'chat_id' => "ChatID",
        ];
    }

    /**
     * Validates the user existence.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validateUserExistence($attribute)
    {
        $user = User::find()
            ->andWhere([
                'OR',
                ['id' => $this->userIdOrName],
                ['username' => $this->userIdOrName]
            ])
            ->one();
        if (empty($user)) {
            return $this->addError($attribute, "User ID / Username doesn't exists.");
        }
    }

    public function getLinkedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'link_user_id']);
    }

    public function getUsername() {
        return $this->username;
    }

    public function getFlag() {
        return $this->flag;
    }

    public function setFlag($flag) {
        $this->flag = $flag;
    }

    public function getChatId() {
        return $this->chat_id;
    }

    public function setChatId($chat_id) {
        $this->chat_id = $chat_id;
    }
}
