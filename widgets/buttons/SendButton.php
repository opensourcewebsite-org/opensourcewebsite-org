<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class SendButton extends PjaxButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = 'btn btn-secondary';
        $this->defaultOptions['style'] = 'color: white;';
        $this->defaultOptions['title'] = Yii::t('app', 'Send');

        if ($this->text == null) {
            $this->text = Yii::t('app', 'Send');
        }
    }

    public function run()
    {
        return parent::run();
    }
}
