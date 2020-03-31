<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;

class SJobController extends Controller
{
    public function actionIndex()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
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
                    ],
                ]
            )
            ->build();
    }
}
