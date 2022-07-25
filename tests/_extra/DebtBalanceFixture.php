<?php

namespace app\tests\_extra;

use app\models\DebtBalance;
use app\tests\fixtures\DebtFixture;
use yii\base\InvalidCallException;
use yii\test\ActiveFixture;

class DebtBalanceFixture extends ActiveFixture
{
    public $modelClass = DebtBalance::class;

    public function load()
    {
        $debtFixture = DebtFixture::class;
        $message = "Don't load this fixture in common way.\n";
        $message .= "Load `$debtFixture` instead - it will generate proper `$this->modelClass`.\n";

        throw new InvalidCallException($message);
    }
}
