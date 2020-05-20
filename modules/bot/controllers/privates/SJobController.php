<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\modules\bot\components\Controller;

/**
 * Class SJobController
 *
 * @package app\modules\bot\controllers
 */
class SJobController extends Controller
{
    public function actionIndex()
    {

        return $this->getResponseBuilder()

        

            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'isNotificationsEnabled' => true,
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Resumes'),
                            'callback_data' => ResumesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Vacancies'),
                            'callback_data' => CompaniesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Companies'),
                            'callback_data' => CompaniesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => ServicesController::createRoute(),
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => '📱',
                        ],
                    ],
                ]
            )
            ->build();
    }
}
