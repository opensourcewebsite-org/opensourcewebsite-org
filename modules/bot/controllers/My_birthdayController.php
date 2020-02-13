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
     * @return array
     */
    public function actionIndex()
    {
        $botClient = $this->getBotClient();
        $user = $this->getUser();

        $birthday = $user->birthday;

        if (!isset($birthday)) {
            $botClient->getState()->setName('/set_birthday');
            $botClient->save();
        }

        return [
            new SendMessageCommand(
                $this->getUpdate()->getMessage()->getChat()->getId(),
                $this->render('index', [
                    'birthday' => isset($birthday)
                        ? (new \DateTime($birthday))->format(User::DATE_FORMAT)
                        : null,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => isset($birthday)
                        ? new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/change_birthday',
                                    'text' => Yii::t('bot', 'Change Birthday'),
                                ]
                            ]
                        ])
                        : null,
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
        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = \Yii::$app->formatter->format($text, 'date');
            $user->save();
            $botClient->getState()->setName(null);
            $botClient->save();
        }

        return $this->actionIndex();
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();

        $botClient->getState()->setName('/set_birthday');
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
