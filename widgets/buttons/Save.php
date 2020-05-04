<?php

namespace app\widgets\buttons;

use yii\helpers\Html;
use yii\base\Widget;
use Yii;

class Save extends Widget
{

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return Html::submitButton(Yii::t('app', 'Save'), [
            'class' => 'btn btn-success',
            'title' => Yii::t('app', 'Save')]);
    }
}
