<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class TelegramController
 *
 * @package app\modules\bot\controllers\privates
 */
class TelegramController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => GroupController::createRoute(),
                            'text' => Yii::t('bot', 'Groups'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute(),
                            'text' => Yii::t('bot', 'Channels'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
