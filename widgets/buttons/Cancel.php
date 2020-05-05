<?php

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;
use Yii;

class Cancel extends LinkButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = 'btn btn-secondary';
        $this->defaultOptions['style'] = 'color: white;';
        $this->defaultOptions['data-dismiss'] = 'modal';
        $this->defaultOptions['title'] = Yii::t('app', 'Cancel');

        if($this->text == null) {
            $this->text = 'Cancel';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
