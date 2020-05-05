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
        $this->defaultOptions['title'] = 'Edit';
        $this->defaultOptions['class'] = Yii::t('app', 'edit-btn');
    }

    public function run()
    {
        return parent::run();
    }
}
