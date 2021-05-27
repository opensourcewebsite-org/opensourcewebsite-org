<?php
declare(strict_types=1);

namespace app\widgets\CompanySelectCreatable;

use app\models\Company;
use app\widgets\base\Widget;
use kartik\select2\Select2Asset;
use yii\base\Model;

class CompanySelectCreatable extends Widget {


    public array $companies = [];

    public array $pluginOptions = ['allowClear' => true];

    private array $defaultOptions = ['class' => 'form-control flex-grow-1', 'prompt' => ''];

    public function run(): string
    {
        return $this->render('control', [
            'hasModel' => $this->hasModel(),
            'model' => $this->model,
            'name'=> $this->name,
            'attribute' => $this->attribute,
            'companies' => $this->companies,
            'options' => array_merge($this->defaultOptions, $this->options),
            'value' => $this->value,
            'controlId' => $this->id,
            'companyModel' => new Company(),
            'pluginOptions' => $this->pluginOptions,
        ] );
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function registerAssets()
    {
        Select2Asset::register($this->getView());
    }
}
