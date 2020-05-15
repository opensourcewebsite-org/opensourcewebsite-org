<?php

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class SaveButton extends PjaxButton
{

    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = 'btn btn-success';
        $this->defaultOptions['style'] = 'color: white;';
        $this->defaultOptions['title'] = Yii::t('common', 'Save');

        if ($this->text == null) {
            $this->text = 'Save';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
