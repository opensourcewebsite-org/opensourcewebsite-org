<?php

namespace app\widgets;

use app\widgets\traits\ModalTrait;

class ModalAjax extends \lo\widgets\modal\ModalAjax
{
    use ModalTrait;

    public function initOptions()
    {
        parent::initOptions();
        $this->initOptionsExt();
    }
}
