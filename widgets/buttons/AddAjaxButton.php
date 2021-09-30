<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class AddAjaxButton extends PjaxButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = 'btn btn-success';
        $this->defaultOptions['style'] = 'color: white;';
        $this->defaultOptions['title'] = Yii::t('app', 'Add');

        if ($this->text == null) {
            $this->text = 'Add';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
