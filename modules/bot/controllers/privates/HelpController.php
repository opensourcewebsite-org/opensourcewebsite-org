<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class HelpController
 *
 * @package app\modules\bot\controllers\privates
 */
class HelpController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => StartController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ]
                ]
            )
            ->build();
    }
}
