<?php

namespace app\modules\bot\models;

class UserState
{
    private $fields = [];


    public function getKeyboardButtons()
    {
        return isset($this->fields['keyboardButtons']) ? $this->fields['keyboardButtons'] : [];
    }

    public function setKeyboardButtons($value)
    {
        $this->fields['keyboardButtons'] = $value;
    }

    public function getName()
    {
        return isset($this->fields['name']) ? $this->fields['name'] : null;
    }

    public function setName($value)
    {
        $this->fields['name'] = $value;
    }

    public function getEmail()
    {
        return isset($this->fields['email']) ? $this->fields['email'] : null;
    }

    public function setEmail($value)
    {
        $this->fields['email'] = $value;
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }

    public static function fromJson($json)
    {
        $state = new UserState();
        $state->fields = json_decode($json, true);
        return $state;
    }
}
