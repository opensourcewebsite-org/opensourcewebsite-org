<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use Yii;
use yii\bootstrap4\Html;
use yii\bootstrap4\Widget;

class SubmitButton extends Widget
{
    private array $defaultOptions = ['class' => 'btn btn-success'];

    public function run()
    {
        return Html::submitButton(Yii::t('app', 'Save'), array_merge($this->options, $this->defaultOptions));
    }
}
