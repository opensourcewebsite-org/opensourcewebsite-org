<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class MyAccountController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyAccountController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->clearInputRoute();

        $user = $this->getTelegramUser();
        $globalUser = $this->getUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('/my-profile/index', [
                    'telegramUser' => $user,
                    'user' => $globalUser,
                ]),
                [
                    [
                        [
                            'callback_data' => MyRatingController::createRoute(),
                            'text' => Yii::t('bot', 'Rating'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyWebsiteAccountController::createRoute(),
                            'text' => Yii::t('bot', 'Website account'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
