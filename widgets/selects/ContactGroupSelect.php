<?php

declare(strict_types=1);

namespace app\widgets\selects;

use app\components\helpers\ArrayHelper;
use app\models\ContactGroup;
use app\widgets\base\Widget;
use kartik\select2\Select2;
use yii\base\Model;
use yii\web\JsExpression;
use yii\helpers\Url;
use Yii;

class ContactGroupSelect extends Widget
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
                'data' => $this->getGroups(),
                'showToggleAll' => false,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions,
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->getGroups(),
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
        $createUrl = Url::to('/contact/create-group-ajax');

        $this->getView()->registerJs(new JsExpression("
            $('#{$this->getId()}').on('select2:select', function(e){
                if (e.params.data.newTag) {
                    const name = e.params.data.text;
                    $.post('{$createUrl}', {'ContactGroup[name]': name}, function(res) {
                       const currentData = $(e.target).val();

                       let newData = currentData.filter( (el) => el !== name );
                       newData.push(res.id);

                       $(e.target).find('option[value=\"'+name+'\"]').remove();
                       $(e.target).append(new Option(name, res.id, true, true));
                       $(e.target).val(newData).trigger('change');
                    });
                }
            });
        "));
    }


    private function getGroups(): array
    {
        return ArrayHelper::map(Yii::$app->user->identity->getContactGroups()->asArray()->all(), 'id', 'name');
    }

    private function preparePluginOptions(): array
    {
        return [
            'tags' => true,
            'allowClear' => true,
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
