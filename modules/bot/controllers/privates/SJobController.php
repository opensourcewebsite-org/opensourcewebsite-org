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
                    'callback_data' => SJobResumeController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Vacancies'),
                    'callback_data' => SJobVacancyController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Emoji::JOB_COMPANY . ' ' . Yii::t('bot', 'Companies'),
                    'callback_data' => SJobCompanyController::createRoute(),
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
