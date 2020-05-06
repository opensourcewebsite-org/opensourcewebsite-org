<?php

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;
use app\components\helpers\Icon;
use Yii;

class Edit extends LinkButton
{
    public function init()
    {
        parent::init();

        $this->text = Icon::EDIT;
        $this->defaultOptions['title'] = Yii::t('app', 'Edit');
        $this->defaultOptions['class'] = 'edit-btn';
    }

    public function run()
    {
        return parent::run();
    }
}
