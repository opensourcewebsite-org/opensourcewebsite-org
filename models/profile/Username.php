<?php

namespace app\models\profile;

use app\models\User;
use yii\base\Model;
use Yii;

class Username extends Model
{

    /** @var string */
    public $username;

    public $currentUsername;

    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'trim'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/i', 'message' => 'Username must contain letters, numbers and _'],
            ['username', 'validateUsernameUnique'],
        ];
    }

    public function init()
    {
        $this->currentUsername = Yii::$app->user->identity->username;
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username (optional)',
        ];
    }

    public function validateUsernameUnique()
    {
        if (is_numeric($this->username)) {
            $this->addError('username', 'User name can\'t be number');
        }

        if (strcasecmp($this->currentUsername, $this->username) !== 0) {
            $isUserInDB = User::findOne(['username' => $this->username]);
            if ($isUserInDB) {
                $this->addError('username', 'User name must be unique.');
            }
        }
    }
}
