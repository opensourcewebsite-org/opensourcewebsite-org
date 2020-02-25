<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\EditMessageReplyMarkupCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\models\User;
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
            $this->getState()->setName('/my_birthday_create');
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
                                'callback_data' => '/my_birthday_update',
                                'text' => '✏️',
                            ]
                        ] : []),
                        [
                            [
                                'callback_data' => '/my_profile',
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
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = \Yii::$app->formatter->format($text, 'date');
            $user->save();
            $this->getState()->setName(null);

            return $this->actionIndex();
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                ]
            )
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();

        $this->getState()->setName('/my_birthday_create');

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
                                'callback_data' => '/my_birthday',
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
