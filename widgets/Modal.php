<?php

namespace app\widgets;

use app\widgets\traits\ModalTrait;

class Modal extends \yii\bootstrap\Modal
{
    use ModalTrait;

    public function initOptions()
    {
        parent::initOptions();
        $this->initOptionsExt();
    }
}
