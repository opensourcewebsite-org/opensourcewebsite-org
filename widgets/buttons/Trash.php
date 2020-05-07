<?php

namespace app\widgets\buttons;

use app\components\helpers\Icon;
use app\widgets\base\PjaxButton;
use Yii;

class Trash extends PjaxButton
{

    public function init()
    {
        parent::init();

        $this->confirm = true;
        $this->text = Icon::TRASH;
        $this->defaultOptions['title'] = Yii::t('app', 'Delete');
    }

    public function run()
    {
        return parent::run();
    }
}
