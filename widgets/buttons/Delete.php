<?php

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;
use Yii;

class Delete extends LinkButton
{

    public function init()
    {
        parent::init();

        $this->confirm = true;

        $this->addClass .= 'btn btn-danger float-right ';

        $this->text = Yii::t('app', 'Delete');
    }

    public function run()
    {
        return parent::run();
    }
}
