<?php

namespace app\modules\bot\controllers\privates;

use app\models\User as GlobalUser;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use DateTime;
use Yii;

/**
 * Class MyBirthdayController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyBirthdayController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->setName(null);

        $user = $this->getUser();

        if (!$user->birthday) {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'birthday' => $user->birthday,
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
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate()
    {
        $this->getState()->setName(self::createRoute('update'));

        $user = $this->getUser();

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($this->validateDate($text, GlobalUser::DATE_FORMAT)) {
                    $user->birthday = Yii::$app->formatter->format($text, 'date');
                    $user->save();

                    return $this->actionIndex();
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => ($user->birthday ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    private function validateDate($date, $format)
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }
}
