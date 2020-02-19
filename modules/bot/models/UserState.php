<?php

namespace app\modules\bot\models;

/**
 * @property array $keyboardButtons
 * @property string $name
 * @property string $email
 */
class UserState
{
    private $fields = [];

    private function __construct()
    {
        $fields['intermediate'] = [];
    }

    public function getKeyboardButtons()
    {
        return $this->fields['keyboardButtons'] ?? [];
    }

    public function setKeyboardButtons(?array $value)
    {
        $this->fields['keyboardButtons'] = $value;
    }

    public function getName()
    {
        return $this->fields['name'] ?? null;
    }

    public function setName(?string $value)
    {
        if ($this->fields['name'] != $value) {
            $this->fields['name'] = $value;
            //$this->fields['intermediate'] = [];
        }
    }

    public function getIntermediateField(string $name, $defaultValue)
    {
        return $this->fields['intermediate'][$name] ?? null;
    }

    public function setIntermediateField(string $name, ?string $value)
    {
        $this->fields['intermediate'][$name] = $value;
    }

    public function save(User $user)
    {
        $user->state = json_encode($this->fields);
        return $user->save();
    }

    public static function fromUser(User $user)
    {
        $state = new UserState();
        if (!empty($user->state)) {
            $state->fields = json_decode($user->state, true);
        }
        return $state;
    }
}
