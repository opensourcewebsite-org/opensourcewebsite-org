<?php

namespace app\widgets\buttons;

use yii\helpers\Html;
use yii\base\Widget;
use Yii;

class Cancel extends Widget
{

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return Html::button(Yii::t('app', 'Cancel'), [
            'class' => 'btn btn-secondary',
            'data-dismiss' => 'modal',
            'title' => Yii::t('app', 'Cancel')]);
    }
}
