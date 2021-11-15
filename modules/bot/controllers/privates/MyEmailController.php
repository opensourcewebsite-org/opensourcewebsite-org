<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\models\User;
use app\models\UserEmail;

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
        $user = $this->getUser();

        if ($userEmail = $user->email) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('index', [
                        'userEmail' => $userEmail,
                        'user' => $user,
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

        return $this->actionUpdate();
    }

    public function actionUpdate()
    {
        $this->getState()->setName(self::createRoute('update'));

        if (!$userEmail = $this->user->email) {
            $userEmail = new UserEmail();
            $userEmail->user_id = $this->user->id;
        }

        if ($this->getUpdate()->getMessage()) {
            $email = $this->getUpdate()->getMessage()->getText();

            if ($userEmail->isNewRecord || ($userEmail->email != $email)) {
                $userEmail->email = $email;
            }

            if ($userEmail->getDirtyAttributes() && $userEmail->save()) {
                unset($this->user->email);
                $this->user->sendConfirmationEmail();
                $this->getState()->setName(null);

                return $this->actionIndex();
            }
        }

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

    public function actionDelete(): array
    {
        if ($userEmail = $this->user->email) {
            $userEmail->delete();
            unset($this->user->email);
        }

        return $this->run('my-profile/index');
    }
}
