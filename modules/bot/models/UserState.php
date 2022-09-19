<?php

namespace app\modules\bot\models;

/**
 * Class UserState
 *
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

    /**
     * @param string $name
     * @param null   $defaultValue
     *
     * @return mixed|null
     */
    public function getIntermediateField(string $name, $defaultValue = null)
    {
        return $this->fields['intermediate'][$name] ?? $defaultValue;
    }

    /**
     * @param array $values
     */
    public function setIntermediateFields($values)
    {
        foreach ($values as $name => $value) {
            $this->fields['intermediate'][$name] = $value;
        }
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function setIntermediateField(string $name, $value)
    {
        $this->fields['intermediate'][$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isIntermediateFieldExists(string $name)
    {
        return array_key_exists($name, $this->fields['intermediate']);
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

    public function resetByModelName($modelName)
    {
        if (isset($this->fields['intermediate'])) {
            $intermediate = $this->fields['intermediate'];
            foreach ($intermediate as $k => $v) {
                if (strpos($k, $modelName) !== false) {
                    unset($intermediate[$k]);
                }
            }
            $this->fields = [
                'intermediate' => $intermediate
            ];
        }

    }

    public function reset()
    {
        if (isset($this->fields['intermediate'])) {
            $intermediate = $this->fields['intermediate'];
            foreach ($intermediate as $k => $v) {
                if (strpos($k, 'virtual') === false) { // Keep state for virtual objects. Model for virtual object must contain 'Virtual' in the name
                    unset($intermediate[$k]);
                }
            }

            if (!empty($intermediate)) {
                $this->fields = [
                    'intermediate' => $intermediate
                ];
            }
            else {
                $this->fields = [];
            }
        }
        else {
            $this->fields = [];
        }
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
