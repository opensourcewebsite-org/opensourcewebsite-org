<?php
declare(strict_types=1);

namespace app\widgets\KeywordsSelect;

use app\components\helpers\ArrayHelper;
use app\models\JobKeyword;
use kartik\select2\Select2;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\ActiveField;
use kartik\select2\Select2Asset;

class KeywordsSelect extends Widget {

    public ?ActiveField $field = null;

    public ?Model $model = null;

    public ?string $attribute = null;

    public ?string $name = null;

    public array $value = [];

    public array $options = [];

    private array $defaultOptions = ['class' => 'form-control', 'multiple' => true, 'placeholder' => 'Select Keywords...'];


    public function init(){
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    public function run(): string
    {
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' => $this->attribute,
                'data' => $this->getKeywords(),
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => [
                    'tags' => true
                ]

            ]);
        } else {
            return Html::dropDownList($this->name, $this->getKeywords(), $this->value, array_merge($this->defaultOptions, $this->options));
        }
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function registerAssets()
    {
        Select2Asset::register($this->getView());
    }

    private function getKeywords(): array
    {
        return ArrayHelper::map(JobKeyword::find()->orderBy('keyword')->asArray()->all(), 'id', 'keyword');
    }
}
