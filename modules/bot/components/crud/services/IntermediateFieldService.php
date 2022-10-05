<?php

namespace app\modules\bot\components\crud\services;

use app\modules\bot\components\Controller;
use app\modules\bot\models\UserState;

/**
 * Class IntermediateFieldService
 *
 * @package app\modules\bot\components\crud\services
 */
class IntermediateFieldService
{
    public const SAFE_ATTRIBUTE = 'safeAttribute';
    public const SAFE_ATTRIBUTE_FLAG = 'safeAttributeFlag';

    /** @var Controller */
    public $controller;
    /** @var UserState */
    public $state;

    /**
     * @param string $modelName $this->getModelName()
     * @param string|array $attributeName
     * @param string|array $value
     */
    public function set($modelName, $attributeName, $value = '')
    {
        if (is_array($attributeName) && !$value) {
            $this->setArray($modelName, $attributeName);
        } else {
            $this->state->setIntermediateField($this->createName($modelName, $attributeName), $value);
        }
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function get($modelName, $attributeName, $defaultValue = null)
    {
        $name = $this->createName($modelName, $attributeName);

        return $this->state->getIntermediateField($name, $defaultValue);
    }

    /**
     * @param string $modelName
     * @param array $values
     */
    private function setArray($modelName, $values)
    {
        $this->state->setIntermediateFields($this->createName($modelName, $values));
    }

    public function reset($modelName = null)
    {
        $backRoute = $this->controller->backRoute->get();
        $endRoute = $this->controller->endRoute->get();
        $safeAttribute = $this->state->getIntermediateField(self::SAFE_ATTRIBUTE);
        $this->state->reset($modelName);
        $this->controller->backRoute->set($backRoute);
        $this->controller->endRoute->set($endRoute);
        $this->state->setIntermediateField(self::SAFE_ATTRIBUTE, $safeAttribute);
    }

    /**
     * @param string $modelName
     * @param string|array $fieldName
     * @return string|array
     */
    public function createName($modelName, $fieldName)
    {
        if (is_array($fieldName)) {
            $names = [];

            foreach ($fieldName as $key => $item) {
                $names[$this->createName($modelName, $key)] = $item;
            }

            return $names;
        }

        return $modelName . $fieldName;
    }

    /**
     * remove flag after check
     */
    public function hasFlag()
    {
        $flag = $this->state->getIntermediateField(self::SAFE_ATTRIBUTE_FLAG, null);
        $this->state->setIntermediateField(self::SAFE_ATTRIBUTE_FLAG, null);

        return $flag;
    }

    /**
     * set flag for check in loop
     */
    public function enableFlag()
    {
        $this->state->setIntermediateField(self::SAFE_ATTRIBUTE_FLAG, true);
    }
}
