<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class SaveButton extends PjaxButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['title'] = Yii::t('app', 'Save');
        $this->defaultOptions['class'] = 'btn btn-success';
        $this->defaultOptions['style'] = 'color: white;';

        if ($this->text == null) {
            $this->text = Yii::t('app', 'Save');
        }
    }

    public function run()
    {
        return parent::run();
    }
}
