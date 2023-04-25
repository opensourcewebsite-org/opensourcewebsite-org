<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use Yii;

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
        $this->getState()->clearInputRoute();

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
                            'text' => Yii::t('bot', 'My groups'),
                        ],
                        [
                            'callback_data' => ChannelController::createRoute(),
                            'text' => Yii::t('bot', 'My channels'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'url' => ExternalLink::getBotToAddGroupLink(),
                            'text' => Emoji::ADD,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
