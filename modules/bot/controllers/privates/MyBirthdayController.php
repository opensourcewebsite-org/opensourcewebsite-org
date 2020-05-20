<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\models\User;
use app\modules\bot\components\Controller;

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

        $birthday = $user->birthday;

        if (isset($birthday)) {
            try {
                $birthday = (new \DateTime($birthday))->format(User::DATE_FORMAT);
            } catch (\Exception $e) {
            }
        }

        if (!isset($birthday)) {
            $this->getState()->setName(self::createRoute('create'));
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('birthday')),
                [
                    array_merge(
                        [

                            [
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                        (isset($birthday) ?
                            [
                                [
                                    'callback_data' => self::createRoute('update'),
                                    'text' => Emoji::EDIT,
                                ]
                            ]
                            : [])
                    ),
                ]
            )
            ->build();
    }

    public function actionCreate()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = Yii::$app->formatter->format($text, 'date');
            $user->save();
            $this->getState()->setName(null);
            return $this->actionIndex();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();

        $this->getState()->setName(self::createRoute('create'));

        return $this->getResponseBuilder()
            ->removeInlineKeyboardMarkup()
            ->sendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
