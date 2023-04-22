<?php

namespace app\behaviors;

/**
 * ConverterBehavior.
 *
 * @property ActiveRecord $owner
 *
 */
abstract class ConverterBehavior extends \yii\base\Behavior
{
    public $attributes = [];

    /**
     * Expose [[$attributes]] readable
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]) || parent::canGetProperty($name, $checkVars);
    }

    /**
     * Expose [[$attributes]] writable
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]) || parent::canSetProperty($name, $checkVars);
    }

    /**
     * Make [[$attributes]] readable
     * @inheritdoc
     */
    public function __get($param)
    {
        if (isset($this->attributes[$param])) {
            //return $this->convertFromStoredFormat($this->owner->__get($this->attributes[$param]));
            return $this->convertFromStoredFormat($this->owner->{$this->attributes[$param]});
        } else {
            return parent::__get($param);
        }
    }

    /**
     * Make [[$attributes]] writable
     * @inheritdoc
     */
    public function __set($param, $value)
    {
        if (isset($this->attributes[$param])) {
            //$this->owner->__set($this->attributes[$param], $this->convertToStoredFormat($value));
            $this->owner->{$this->attributes[$param]} = $this->convertToStoredFormat($value);
        } else {
            parent::__set($param, $value);
        }
    }

    abstract protected function convertToStoredFormat($value);

    abstract protected function convertFromStoredFormat($value);
}
