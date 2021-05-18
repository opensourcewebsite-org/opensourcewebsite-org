<?php

namespace app\widgets\base;

use Yii;

class PjaxButton extends Linkable
{
    /**
     * @var bool
     * enable confirm window
     */
    public $confirm;

    public function init()
    {
        parent::init();

        $this->defaultOptions = [
            'title'         => Yii::t('yii', 'Button'),
            'aria-label'    => Yii::t('yii', 'Button'),
            'data-pjax'     => '1',
            'data-method'   => 'post',
            'class'         => 'btn-action',
        ];
    }

    public function run()
    {
        if ($this->confirm == true) {
            $this->options['data-confirm'] = Yii::t('yii', $this->defaultOptions['confirmMessage']);
        }

        return parent::run();
    }
}
