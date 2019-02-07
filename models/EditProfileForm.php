<?php

namespace app\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;

class EditProfileForm extends Model
{
    /** @var string */
    public $username;

    /** @var string */
    public $name;

    /** @var User */
    private $_user;

    /**
     * @param User $user
     * @param array $config
     */
    public function __construct($user, $config = [])
    {
        parent::__construct($config);

        $this->username = $user->username;
        $this->name = $user->name;
        $this->_user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'username'], 'trim'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/i', 'message' => 'Username must contain letters, numbers and _'],
            ['username', 'validateUsernameUnique'],
            ['name', 'validateNameString'],
        ];
    }

    public function validateUsernameUnique()
    {
        if ($this->username == $this->_user->username) {
            return;
        }

        if (is_numeric($this->username)) {
            $this->addError('username', 'User name can\' be number');
            return;
        }

        $user = User::findOne(['username' => $this->username]);
        if ($user) {
            $this->addError('username', 'User name must be unique.');
        }
    }

    public function validateNameString()
    {
        if ($this->name == $this->_user->name) {
            return;
        }

        if (is_numeric($this->name)) {
            $this->addError('name', 'Name can\' be number');
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => 'User name (optional)',
            'name' => 'Name (optional)',
        ];
    }

    public function getUserId()
    {
        return $this->_user->id;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        $user->username = $this->username;
        $user->name = $this->name;
        if (!$user->save()) {
            throw new ServerErrorHttpException();
        }

        return true;
    }
}
