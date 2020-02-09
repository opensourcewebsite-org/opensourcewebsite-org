<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\EditMessageTextCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
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
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $this->getUpdate()->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('index', [
                        'birthday' => (new \DateTime($this->module->user->birthday))->format(User::DATE_FORMAT)
                    ]),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_birthday',
                                'text' => Yii::t('bot', 'Change Birthday'),
                            ]
                        ]
                    ]),
                ])
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
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('create', [
                        'success' => $success,
                    ]),
                ])
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
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => $this->textFormat,
                    'text' => $update->getCallbackQuery()->getMessage()->getText(),
                ])
            ),
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('update'),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
        ];
    }

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
