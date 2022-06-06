<?php

declare(strict_types=1);

namespace app\widgets\LocationPickerWidget;

use app\assets\LeafletAsset;
use app\assets\LeafletLocateControlAsset;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\ActiveField;

class LocationPickerWidget extends Widget
{
    public ?ActiveField $field;

    public ?Model $model;

    public string $attribute;

    public string $name  = '';

    public string $value;

    public array $options = [];

    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }

        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();

        echo $this->render('view', ['model' => $this->model, 'attribute' => $this->attribute, 'id' => $this->options['id']]);
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    public function registerClientScript()
    {
        $view = $this->getView();
        LeafletAsset::register($view);
        LeafletLocateControlAsset::register($view);
    }
}
