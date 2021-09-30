<?php

# https://github.com/ivankff/yii2-modal-ajax

namespace app\widgets;

use app\widgets\traits\ModalTrait;

class ModalAjax extends \ivankff\yii2ModalAjax\ModalAjax
{
    use ModalTrait;

    public function initOptions()
    {
        parent::initOptions();

        $this->initOptionsExt();
    }

    public function init()
    {
        $this->bootstrapVersion = ModalAjax::BOOTSTRAP_VERSION_4;
        $this->ajaxSubmit = true;

        parent::init();
    }
}
