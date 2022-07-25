<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Currency;
use app\models\Language;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyWebsiteAccountController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyWebsiteAccountController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $this->user,
                ]),
                [
                    [
                        [
                            'url' => $this->user->getAuthLink(),
                            'text' => Yii::t('bot', 'Go to Website account'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
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
