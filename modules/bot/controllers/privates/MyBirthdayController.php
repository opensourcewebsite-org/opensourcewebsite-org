<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\EditMessageReplyMarkupCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\models\User;
use app\modules\bot\components\Controller as Controller;

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
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        $birthday = $user->birthday;

        if (!isset($birthday)) {
            $telegramUser->getState()->setName(self::createRoute('create'));
            $telegramUser->save();
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'birthday' => isset($birthday)
                        ? (new \DateTime($birthday))->format(User::DATE_FORMAT)
                        : null,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        (isset($birthday) ? [
                            [
                                'callback_data' => self::createRoute('update'),
                                'text' => '✏️',
                            ]
                        ] : []),
                        [
                            [
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => '🔙',
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
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = Yii::$app->formatter->format($text, 'date');
            $user->save();
            $telegramUser->getState()->setName(null);
            $telegramUser->save();
            return $this->actionIndex();
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName(self::createRoute('create'));
        $telegramUser->save();

        return [
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => '🔙',
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
