<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use yii\db\ActiveRecord;

/**
 * Class UserState
 *
 * @package app\modules\bot\models
 */
class UserState
{
    private $fields = [];
    /** @var object */
    private $model = null;

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

    public function getBackRoute()
    {
        return $this->fields['backRoute'] ?? null;
    }

    public function setBackRoute(?string $value)
    {
        $this->fields['backRoute'] = $value;
    }

    /**
     * @param string $name
     * @param null $defaultValue
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
     * @param $value
     */
    public function setIntermediateField(string $name, $value)
    {
        $this->fields['intermediate'][$name] = $value;
    }

    /**
     * @param string $name
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

    public function getIntermediateModel($modelClass = null)
    {
        if (isset($this->model) && $modelClass == get_class($this->model)) {
            return $this->model;
        }

        if (isset($this->fields['intermediate'])) {
            $intermediate = $this->fields['intermediate'];
            if (isset($modelClass)) {
                try {
                    $this->model = \Yii::createObject([
                        'class' => $modelClass,
                    ]);

                    if ($this->model instanceof ActiveRecord) {
                        $this->model->setAttributes($intermediate[$this->getModelName($modelClass)], false);

                        return $this->model;
                    }
                } catch (\Throwable $e) {
                    \Yii::error($e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }

    public function setIntermediateModel($model, $modelName = null)
    {
        $attributes = [];

        foreach ($model as $key => $value) {
            $attributes[$key] = $value;
        }

        if (!isset($modelName)) {
            $modelName = $this->getModelName(get_class($model));
        }

        $this->setIntermediateFieldArray($modelName, $attributes);
        $this->model = $model;
    }

    public function clearIntermediateModel($modelClass = null)
    {
        $this->model = null;

        if (isset($modelClass)) {
            unset($this->fields['intermediate'][$this->getModelName($modelClass)]);
        }
    }

    private function getModelName($modelClass): string
    {
        $parts = explode('\\', $modelClass);

        return strtolower(array_pop($parts));
    }

    public function save(User $user)
    {
        $user->state = json_encode($this->fields);

        return $user->save();
    }

    public function reset($modelName = null)
    {
        if (isset($this->fields['intermediate'])) {
            $intermediate = $this->fields['intermediate'];
            if (isset($modelName)) {
                foreach ($intermediate as $k => $v) {
                    if (strpos($k, $modelName) !== false) {
                        unset($intermediate[$k]); // Delete only fields related to the current model
                    }
                }
            }

            if (!empty($intermediate)) {
                $this->fields = [
                    'intermediate' => $intermediate
                ];
            } else {
                $this->fields = [];
            }
        } else {
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
