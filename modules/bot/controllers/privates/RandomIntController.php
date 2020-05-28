<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\Controller;

/**
 * Class RandomIntController
 *
 * @package app\modules\bot\controllers
 */
class RandomIntController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($message = '')
    {
        //TODO add flexible int min and max from $message
        $randomInt = random_int(1, 10);

        return $this->getResponseBuilder()
            ->sendMessage(
                new MessageText($randomInt)
            )
            ->build();
    }
}
