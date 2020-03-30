<?php

namespace app\widgets;

class Modal extends \yii\bootstrap\Modal
{
    public function initOptions()
    {
        parent::initOptions();

        if (empty($this->options['class'])) {
            $this->options['class'] = '';
        }
        $this->options['class'] .= ' modal-fix';

        if ($this->header && $this->header == strip_tags($this->header)) {
            $this->header = "<h5 class='modal-title'>$this->header</h5>";
        }
    }
}
