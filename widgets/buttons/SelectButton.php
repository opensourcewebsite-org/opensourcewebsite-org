<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\PjaxButton;
use Yii;

class SelectButton extends PjaxButton
{
    public bool $confirm = true;

    public function init()
    {
        parent::init();

        $this->confirm = true;
        $this->defaultOptions['confirmMessage'] = 'Are you sure you want to do this?';
        $this->defaultOptions['title'] = Yii::t('app', 'Select');
        $this->defaultOptions['class'] = 'btn btn-default';

        if ($this->text == null) {
            $this->text = Yii::t('app', 'Select');
        }
    }

    public function run()
    {
        if ($this->confirm == true) {
            $this->options['data-confirm'] = Yii::t('yii', $this->defaultOptions['confirmMessage']);
        }

        return parent::run();
    }
}
