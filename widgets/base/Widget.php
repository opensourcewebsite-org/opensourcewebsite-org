<?php

declare(strict_types=1);

namespace app\widgets\base;

use yii\base\Widget as YiiWidget;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\widgets\ActiveField;

abstract class Widget extends YiiWidget
{
    public ?ActiveField $field = null;
    public ?Model $model = null;
    public ?string $attribute = null;
    public ?string $name = null;
    public array $value = [];
    public array $options = [];

    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }

        if (!isset($this->options['id'])) {
            $this->id = $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }

        parent::init();
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }
}
