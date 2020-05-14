<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use app\modules\bot\components\Controller;

/**
 * Class HelpController
 *
 * @package app\controllers\bot
 */
class HelpController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                    ]
                ]
            )
            ->build();
    }
}
