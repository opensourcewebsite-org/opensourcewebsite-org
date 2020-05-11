<?php

namespace app\widgets\traits;

use yii\base\Widget;

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
        //Require:

        if (empty($this->options['class'])) {
            $this->options['class'] = '';
        }
        $this->options['class'] .= ' modal-fix';

        //Defaults:

        if (isset($this->title) && $this->title == strip_tags($this->title)) {
            $this->title = "<h5 class='modal-title'>$this->title</h5>";
        }

        if (isset($this->header) && $this->header == strip_tags($this->header)) {
            $this->header = "<h5 class='modal-title'>$this->header</h5>";
        }

        if (!isset($this->footerOptions['class'])) {
            $this->footerOptions['class'] = 'card-footer';
        }

        $this->jsLinkFooterButtonToForm();
    }

    private function jsLinkFooterButtonToForm()
    {
        $this->on(Widget::EVENT_AFTER_RUN, function () {
            $this->view->registerJs("
                jQuery('#$this->id').on('click', '.modal-footer button[type=\"submit\"]', function (event) {
                    if (jQuery(this).parents('form').length) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    jQuery('#$this->id').find('form').submit();

                    return false;
                });"
            );
        });
    }
}
