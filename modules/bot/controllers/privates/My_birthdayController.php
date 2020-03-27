<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use Yii;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\modules\bot\components\Controller;

/**
 * Class My_birthdayController
 *
 * @package app\modules\bot\controllers
 */
class My_birthdayController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        $birthday = $user->birthday;

        if (!isset($birthday)) {
            $this->getState()->setName('/my_birthday__create');
        } else {
            try {
                $birthday = (new \DateTime($birthday))->format(User::DATE_FORMAT);
            } catch (\Exception $e) {
            }
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', compact('birthday')),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        (isset($birthday)
                            ? [
                                [
                                    'callback_data' => '/my_birthday__update',
                                    'text' => Emoji::EDIT,
                                ]
                            ]
                            : null),
                        [
                            [
                                'callback_data' => My_profileController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]),
                ]
            ),
        ];
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

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update')
            )
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();

        $this->getState()->setName('/my_birthday__create');

        return [
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_birthday',
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
