<?php

namespace app\models\profile;

use app\models\User;
use yii\base\Model;
use Yii;

class Username extends Model
{

    /** @var string */
    public $username;

    /** @var User */
    private $_user;

    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'trim'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/i', 'message' => 'Username must contain letters, numbers and _'],
            ['username', 'validateUsernameUnique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username (optional)',
        ];
    }

    public function init()
    {
        parent::init();

        $this->_user = Yii::$app->user;
        $this->username = $this->_user->identity->username;
    }

    public function validateUsernameUnique()
    {
        if (!strcasecmp($this->username, $this->_user->username)) {
            return;
        }

        if (is_numeric($this->username)) {
            $this->addError('username', 'User name can\'t be number');
            return;
        }

        $user = User::findOne(['username' => $this->username]);
        if ($user) {
            $this->addError('username', 'User name must be unique.');
        }
    }
}
