<?php

namespace app\modules\bot\models;

class UserState
{
    private $_fields = [];


    public function getKeyboardButtons()
    {
        return isset($this->_fields['keyboardButtons']) ? $this->_fields['keyboardButtons'] : [];
    }

    public function setKeyboardButtons($value)
    {
        $this->_fields['keyboardButtons'] = $value;
    }

    public function getName()
    {
        return isset($this->_fields['name']) ? $this->_fields['name'] : null;
    }

    public function setName($value)
    {
        $this->_fields['name'] = $value;
    }

    public function getEmail()
    {
        return isset($this->_fields['email']) ? $this->_fields['email'] : null;
    }

    public function setEmail($value)
    {
        $this->_fields['email'] = $value;
    }

    public function toJson()
    {
        return json_encode($this->_fields);
    }

    public static function fromJson($json)
    {
        $state = new UserState();
        $state->_fields = json_decode($json, true);
        return $state;
    }
}
