<?php

namespace app\modules\bot\controllers\privates;

use app\models\User;
use app\models\UserEmail;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use Yii;

/**
 * Class MyEmailController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyEmailController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        if (!$userEmail = $this->globalUser->userEmail) {
            return $this->actionUpdate();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'userEmail' => $userEmail,
                    'user' => $this->getGlobalUser(),
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'callback_data' => self::createRoute('update'),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => self::createRoute('delete'),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate()
    {
        $this->getState()->setName(self::createRoute('input'));

        $userEmail = $this->globalUser->userEmail ?: $this->globalUser->newUserEmail;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => ($userEmail->isNewRecord ? MyProfileController::createRoute() : self::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionInput()
    {
        $userEmail = $this->globalUser->userEmail ?: $this->globalUser->newUserEmail;

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $userEmail->email = $text;

                if ($userEmail->validate()) {
                    $userEmail->save(false);
                    unset($this->globalUser->userEmail);
                    $this->globalUser->sendConfirmationEmail();

                    return $this->actionIndex();
                }
            }
        }
    }

    public function actionDelete()
    {
        if ($userEmail = $this->globalUser->userEmail) {
            $userEmail->delete();
            unset($this->globalUser->userEmail);
        }

        return $this->run('my-profile/index');
    }
}
