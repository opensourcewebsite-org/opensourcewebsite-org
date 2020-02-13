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
        return $this->_fields['name'];
    }

    public function setName($value)
    {
        $this->_fields['name'] = $value;
    }

    public function toJson()
    {
        return json_encode($this->_fields);
    }

    public static function fromJson($json)
    {
        $state = new UsertState();
        $state->_fields = json_decode($json, true);
        return $state;
    }
}
