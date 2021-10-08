<?php

declare(strict_types=1);

namespace app\widgets\AdKeywordsSelect;

use app\components\helpers\ArrayHelper;
use app\models\AdKeyword;
use app\widgets\base\Widget;
use kartik\select2\Select2;
use yii\base\Model;
use yii\web\JsExpression;
use yii\helpers\Url;

class AdKeywordsSelect extends Widget
{
    private array $defaultOptions = [
        'class' => 'form-control',
        'multiple' => true,
        'placeholder' => 'Select...',
    ];

    private array $pluginOptions = [];

    public function init()
    {
        $this->pluginOptions = $this->preparePluginOptions();

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
                'showToggleAll' => false,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions,
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->getKeywords(),
                'showToggleAll' => false,
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions,
            ]);
        }
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function registerJs()
    {
        $createUrl = Url::to('/ad-keyword/create-ajax');

        $this->getView()->registerJs(new JsExpression("
            $('#{$this->getId()}').on('select2:select', function(e){
                if (e.params.data.newTag) {
                    const keyword = e.params.data.text;
                    $.post('{$createUrl}', {'AdKeyword[keyword]': keyword}, function(res) {
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


    private function getKeywords(): array
    {
        return ArrayHelper::map(AdKeyword::find()->orderBy(['keyword' => SORT_ASC])->asArray()->all(), 'id', 'keyword');
    }

    private function preparePluginOptions(): array
    {
        return [
            'tags' => true,
            'createTag' => new JsExpression("function(tag) {
                            return {
                                id: tag.term,
                                text: tag.term,
                                newTag: true
                            };
                        }
                    "),
        ];
    }
}
