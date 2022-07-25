<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;
use Yii;

class CancelButton extends LinkButton
{
    public function init()
    {
        parent::init();

        $this->defaultOptions['class'] = 'btn btn-secondary';
        $this->defaultOptions['style'] = 'color: white;';
        $this->defaultOptions['data-dismiss'] = 'modal';
        $this->defaultOptions['title'] = Yii::t('app', 'Cancel');

        if ($this->text == null) {
            $this->text = Yii::t('app', 'Cancel');
        }
    }

    public function run()
    {
        return parent::run();
    }
}
