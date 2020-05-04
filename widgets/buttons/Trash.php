<?php

namespace app\widgets\buttons;

use app\widgets\base\Button;

class Trash extends Button
{

    public function init()
    {
        parent::init();

        $this->text = '<i class="far fa-trash-alt"></i>';

    }

    public function run()
    {
        return parent::run();
    }
}
