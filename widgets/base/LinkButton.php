<?php

declare(strict_types=1);

namespace app\widgets\base;

use Yii;

class LinkButton extends Linkable
{
    public function init()
    {
        parent::init();

        $this->defaultOptions = [
            'title' => Yii::t('yii', 'Button'),
            'class' => 'btn btn-action',
        ];
    }

    public function run()
    {
        return parent::run();
    }
}
