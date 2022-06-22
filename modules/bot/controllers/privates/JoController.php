<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class JoController
 *
 * @package app\modules\bot\controllers\privates
 */
class JoController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $buttons = [
            [
                [
                    'text' => Emoji::JO_RESUME . ' ' . Yii::t('bot', 'Resumes'),
                    'callback_data' => JoResumeController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Emoji::JO_VACANCY . ' ' . Yii::t('bot', 'Vacancies'),
                    'callback_data' => JoVacancyController::createRoute(),
                ],
            ],
            [
                [
                    'text' => Emoji::JO_COMPANY . ' ' . Yii::t('bot', 'Companies'),
                    'callback_data' => JoCompanyController::createRoute(),
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
