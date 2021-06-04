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
use yii\web\JsExpression;
use yii\widgets\ActiveField;
use kartik\select2\Select2Asset;
use yii\helpers\Url;

class KeywordsSelect extends Widget {

    public ?ActiveField $field = null;

    public ?Model $model = null;

    public ?string $attribute = null;

    public ?string $name = null;

    public array $value = [];

    public array $options = [];

    private array $defaultOptions = ['class' => 'form-control', 'multiple' => true, 'placeholder' => 'Select Keywords...'];

    private array $pluginOptions = [];

    public function init(){
        $this->pluginOptions = $this->preparePluginOptions();

        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
            $this->id = $this->options['id'];
        }
        parent::init();
    }

    public function run(): string
    {

        $this->registerJs();
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' => $this->attribute,
                'data' => $this->getKeywords(),
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->getKeywords(),
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions
            ]);
        }
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function registerJs(){
        $keywordCreateUrl = Url::to('/job-keyword/create-ajax');

        $this->getView()->registerJs(new JsExpression("
            $('#{$this->getId()}').on('select2:select', function(e){
                if (e.params.data.newKeyword) {
                    const keyword = e.params.data.text;
                    $.post('{$keywordCreateUrl}', {'JobKeyword[keyword]': keyword}, function(res) {
                       const currentData = $(e.target).val();

                       let newData = currentData.filter( (el) => el !== keyword );
                       newData.push(res.id);

                       $(e.target).find('option[value=\"'+keyword+'\"]').remove();
                       $(e.target).append(new Option(keyword, res.id, true, true));
                       $(e.target).val(newData).trigger('change');
                    });
                }
            });
        "));
    }

    private function registerAssets()
    {
        Select2Asset::register($this->getView());
    }

    private function getKeywords(): array
    {
        return ArrayHelper::map(JobKeyword::find()->orderBy(['keyword' => SORT_ASC])->asArray()->all(), 'id', 'keyword');
    }

    private function preparePluginOptions(): array
    {
        return [
            'tags' => true,
            'createTag' => new JsExpression("function(tag) {
                            return {
                                id: tag.term,
                                text: tag.term,
                                newKeyword: true
                            };
                        }
                    "),
        ];
    }
}
