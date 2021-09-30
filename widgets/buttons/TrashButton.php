<?php

declare(strict_types=1);

namespace app\widgets\buttons;

use app\components\helpers\Icon;
use app\widgets\base\PjaxButton;
use Yii;

class TrashButton extends PjaxButton
{
    public bool $confirm = true;

    public function init()
    {
        parent::init();

        $this->confirm = true;
        $this->text = Icon::TRASH;
        $this->defaultOptions['title'] = Yii::t('app', 'Delete');
        $this->defaultOptions['confirmMessage'] = 'Are you sure you want to delete this item?';
    }

    public function run()
    {
        if ($this->confirm == true) {
            $this->options['data-confirm'] = Yii::t('yii', $this->defaultOptions['confirmMessage']);
        }

        return parent::run();
    }
}
