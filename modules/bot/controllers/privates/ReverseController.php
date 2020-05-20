<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\MessageText;

use app\modules\bot\components\Controller;

/**
 * Class ReverseController
 *
 * @package app\modules\bot\controllers
 */
class ReverseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($message = '')
    {
        //TODO add reverse for $$message


        return $this->getResponseBuilder()

        

            ->sendMessage(
                new MessageText($message)
            )
            ->build();
    }
}
