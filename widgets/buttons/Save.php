<?php

namespace app\widgets\buttons;

use app\widgets\base\SubmitButton;
use Yii;

class Save extends SubmitButton
{

    public function init()
    {
        parent::init();

        $this->confirm = false;

        $this->addClass .= 'btn btn-success';

        $this->text = Yii::t('app', 'Save');

        if ($this->addStyle == null) {
            $this->addStyle['color'] = 'white';
        } elseif (is_array($this->addStyle)) {
            $this->addStyle['color'] = 'white';
        } else {
            $this->addStyle .= 'color: white;';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
