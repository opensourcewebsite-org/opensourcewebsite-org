<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\components\helpers\Html;
use app\widgets\base\LinkButton;
use Yii;

class AddButton extends LinkButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = ['btn', 'btn-outline-success'];
        $this->defaultOptions['title'] = Yii::t('app', 'Add');

        if ($this->text == null) {
            $this->text = Html::icon('add');
        }
    }

    public function run()
    {
        return parent::run();
    }
}
