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
            return $this->actionSet();
        }

        $this->getState()->clearInputRoute();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'userEmail' => $userEmail,
                    'user' => $this->globalUser,
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
                            'callback_data' => self::createRoute('set'),
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

    public function actionSet()
    {
        $this->getState()->setInputRoute(self::createRoute('set'));

        $userEmail = $this->globalUser->userEmail ?: $this->globalUser->newUserEmail;

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($userEmail->isNewRecord || ($userEmail->email != $text)) {
                    $userEmail->email = $text;

                    if ($userEmail->save()) {
                        unset($this->globalUser->userEmail);
                        $this->globalUser->sendConfirmationEmail();

                        return $this->actionIndex();
                    }
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                [
                    [
                        [
                            'callback_data' => (!$userEmail->isNewRecord ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
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
