<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\models\User;

/**
 * Class My_birthdayController
 *
 * @package app\modules\bot\controllers
 */
class My_birthdayController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getUpdate()->getMessage()->getChat()->getId(),
                $this->render('index', [
                    'birthday' => (new \DateTime($this->module->user->birthday))->format(User::DATE_FORMAT),
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_birthday',
                                'text' => Yii::t('bot', 'Change Birthday'),
                            ]
                        ]
                    ]),
                ]
            ),
        ];
    }

    public function actionCreate()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        if ($success = $this->validateDate($text, User::DATE_FORMAT))
        {
            $user->birthday = $text;
            $user->save();
            $botClient->resetState();
            $botClient->save();
        }

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('create', [
                    'success' => $success,
                ]),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();

        $botClient->setState([
            'state' => '/set_birthday',
        ]);
        $botClient->save();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $update->getCallbackQuery()->getMessage()->getText(),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
            new SendMessageCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
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
