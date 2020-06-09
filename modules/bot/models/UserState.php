<?php

namespace app\modules\bot\models;

/**
 * Class UserState
 * @package app\modules\bot\models
 */
class UserState
{
    private $fields = [];

    private function __construct()
    {
        $fields['intermediate'] = [];
    }

    public function getName()
    {
        return $this->fields['name'] ?? null;
    }

    public function setName(?string $value)
    {
        $this->fields['name'] = $value;
    }

    public function getIntermediateField(string $name, $defaultValue = null)
    {
        return $this->fields['intermediate'][$name] ?? $defaultValue;
    }

    public function setIntermediateField(string $name, ?string $value)
    {
        $this->fields['intermediate'][$name] = $value;
    }

    public function getIntermediateFieldArray(string $name, $defaultValue = null)
    {
        return $this->fields['intermediate'][$name] ?? $defaultValue;
    }

    public function setIntermediateFieldArray(string $name, ?array $value)
    {
        $this->fields['intermediate'][$name] = $value;
    }

    public function save(User $user)
    {
        $user->state = json_encode($this->fields);
        return $user->save();
    }

    public function reset()
    {
        $this->fields = [];
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
