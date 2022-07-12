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
                            'callback_data' => PublicGroupController::createRoute(),
                            'text' => Yii::t('bot', 'Public groups'),
                        ],
                        [
                            'callback_data' => PublicChannelController::createRoute(),
                            'text' => Yii::t('bot', 'Public channels'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute(),
                            'text' => Yii::t('bot', 'Your groups'),
                        ],
                        [
                            'callback_data' => ChannelController::createRoute(),
                            'text' => Yii::t('bot', 'Your channels'),
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
