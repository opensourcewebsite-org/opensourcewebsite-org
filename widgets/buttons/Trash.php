<?php

namespace app\widgets\buttons;

use app\widgets\base\LinkButton;

class Trash extends LinkButton
{

    public function init()
    {
        parent::init();

        $this->confirm = true;

        $this->text = '<i class="far fa-trash-alt"></i>';
    }

    public function run()
    {
        return parent::run();
    }
}
