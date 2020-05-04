<?php

namespace app\widgets\buttons;

use app\widgets\base\Button;
use Yii;

class Delete extends Button
{

    public function init()
    {
        parent::init();

        $this->addClass .= 'btn btn-danger float-right ';

        $this->text = Yii::t('app', 'Delete');
    }

    public function run()
    {
        return parent::run();
    }
}
