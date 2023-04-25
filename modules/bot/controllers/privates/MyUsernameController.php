<?php

namespace app\modules\bot\controllers\privates;

use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class MyUsernameController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyUsernameController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        if (!$this->globalUser->username) {
            return $this->actionSet();
        }

        $this->getState()->clearInputRoute();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
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
                    ],
                ]
            )
            ->build();
    }

    public function actionSet()
    {
        $this->getState()->setInputRoute(self::createRoute('set'));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $this->globalUser->username = $text;

                if ($this->globalUser->validate('username')) {
                    $this->globalUser->save(false);

                    return $this->actionIndex();
                } else {
                    $this->globalUser->refresh();
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                [
                    [
                        [
                            'callback_data' => ($this->globalUser->username ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
