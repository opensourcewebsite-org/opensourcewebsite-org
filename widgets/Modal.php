<?php

namespace app\widgets;

use app\widgets\traits\ModalTrait;

class Modal extends \yii\bootstrap4\Modal
{
    use ModalTrait;

    public function initOptions()
    {
        parent::initOptions();
        $this->initOptionsExt();
    }
}
