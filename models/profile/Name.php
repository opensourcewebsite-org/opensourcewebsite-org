<?php

namespace app\models\profile;

use app\models\User;
use yii\base\Model;
use Yii;

class Name extends Model
{

    /** @var string */
    public $name;

    /** @var User */
    private $_user;

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'trim'],
            ['name', 'validateNameString'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Name (optional)',
        ];
    }

    public function init()
    {
        parent::init();

        $this->_user = Yii::$app->user;
        $this->name = $this->_user->identity->name;
    }

    public function validateNameString()
    {
        if ($this->name == $this->_user->name) {
            return;
        }

        if (is_numeric($this->name)) {
            $this->addError('name', 'Name can\'t be number');
        }
    }
}
