<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class SJobController
 *
 * @package app\modules\bot\controllers\privates
 */
class SJobController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $buttons = [
            [
                [
                    'text' => Emoji::JOB_RESUME . ' ' . Yii::t('bot', 'Resumes'),
                    'callback_data' => ResumeController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Vacancies'),
                    'callback_data' => VacancyController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Yii::t('bot', 'Companies'),
                    'callback_data' => CompanyController::createRoute(),
                ],
            ],
            [
                [
                    'callback_data' => MenuController::createRoute(),
                    'text' => Emoji::MENU,
                ],
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'isNotificationsEnabled' => true,
                ]),
                $buttons
            )
            ->build();
    }
}
