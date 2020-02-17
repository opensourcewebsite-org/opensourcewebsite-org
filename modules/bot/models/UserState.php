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
        $this->fields['name'] = $value;
    }

    public function getEmail()
    {
        return $this->fields['email'] ?? null;
    }

    public function setEmail(?string $value)
    {
        $this->fields['email'] = $value;
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
