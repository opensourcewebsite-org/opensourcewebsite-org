<?php

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class DeleteButton extends PjaxButton
{

    public function init()
    {
        parent::init();

        $this->confirm = true;
        $this->defaultOptions['title'] = Yii::t('app', 'Delete');
        $this->defaultOptions['class'] = 'btn btn-danger float-right';
        $this->defaultOptions['confirmMessage'] = 'Are you sure you want to delete this item?';

        if ($this->text == null) {
            $this->text = 'Delete';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
