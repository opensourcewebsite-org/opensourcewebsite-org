<?php

namespace app\widgets\traits;

/**
 * Fix and improvement for {@see \yii\bootstrap\Modal}
 *
 * Usage:
 * ```php
 * use ModalTrait;
 *
 * public function initOptions()
 * {
 *     parent::initOptions();
 *     $this->initOptionsExt();
 * }
 * ```
 */
trait ModalTrait
{
    protected function initOptionsExt()
    {
        if (empty($this->options['class'])) {
            $this->options['class'] = '';
        }
        $this->options['class'] .= ' modal-fix';

        if ($this->header && $this->header == strip_tags($this->header)) {
            $this->header = "<h5 class='modal-title'>$this->header</h5>";
        }
    }

}