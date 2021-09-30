<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;
use app\components\helpers\Icon;
use Yii;

class EditButton extends LinkButton
{
    public bool $ajax = false;

    public function init()
    {
        parent::init();

        $this->defaultOptions['title'] = Yii::t('app', 'Edit');
        $this->defaultOptions['class'] = 'edit-btn';

        if ($this->text == null) {
            $this->text = Icon::EDIT;
        }

        if ($this->ajax) {
            $this->defaultOptions['class'] .= ' modal-btn-ajax';
        }
    }

    public function run()
    {
        return parent::run();
    }
}
