<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;

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

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                new MessageText($randomInt),
            )
            ->build();
    }
}
