<?php

namespace app\widgets\buttons;

use app\components\helpers\Icon;
use app\widgets\base\LinkButton;
use Yii;

class AddButton extends LinkButton
{
    public function init()
    {
        parent::init();

        if ($this->text == null) {
            $this->text = Icon::ADD;
        }
        $this->defaultOptions['class'] = 'btn btn-outline-success';
        $this->defaultOptions['title'] = Yii::t('app', 'Add');
    }

    public function run()
    {
        return parent::run();
    }
}
