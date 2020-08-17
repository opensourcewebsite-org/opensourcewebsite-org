<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyBirthdayController
 *
 * @package app\modules\bot\controllers
 */
class MyBirthdayController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        if ($user->birthday) {
            $birthday = $user->birthday;
            try {
                $birthday = (new \DateTime($birthday))->format(User::DATE_FORMAT);
            } catch (\Exception $e) {
            }
        } else {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'birthday' => $birthday,
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
        $this->getState()->setName(self::createRoute('search'));
        $user = $this->getUser();

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

    public function actionSearch()
    {
        $text = $this->getUpdate()->getMessage()->getText();
        $user = $this->getUser();

        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = Yii::$app->formatter->format($text, 'date');
            $user->save();

            $this->getState()->setName(null);

            return $this->actionIndex();
        }
    }

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
