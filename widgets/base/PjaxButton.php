<?php

declare(strict_types=1);

namespace app\widgets\base;

use Yii;

class PjaxButton extends Linkable
{
    public function init()
    {
        parent::init();

        $this->defaultOptions = [
            'title' => Yii::t('yii', 'Button'),
            'aria-label' => Yii::t('yii', 'Button'),
            'data-pjax' => '1',
            'data-method' => 'post',
            'class' => 'btn-action',
        ];
    }

    public function run()
    {
        return parent::run();
    }
}
