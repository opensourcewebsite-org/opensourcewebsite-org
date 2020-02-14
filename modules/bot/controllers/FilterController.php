<?php

namespace app\modules\bot\controllers;

use app\models\BotMessageFilter;
use app\modules\bot\components\message\MessageHandler;
use app\modules\bot\components\ReplyKeyboardManager;
use app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use yii\filters\AccessControl;

/**
 * Class FilterController
 *
 * @package app\controllers\bot
 * @property MessageHandler $messageHandler
 */
class FilterController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            return $this->messageHandler->isAdminMessage();
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }

    public function actionAdd($message = null)
    {
        $chatId = $this->messageHandler->getMessage()->getChat()->getId();
        $userId = $this->messageHandler->getMessage()->getFrom()->getId();
        BotMessageFilter::addWord($message, $chatId, $userId);
        $customKeyboard = BotMessageFilter::getKeyBoardList($chatId);
        $replyMarkup = new ReplyKeyboardMarkup($customKeyboard, false, true);

        $message = new SendMessageCommand(
            $chatId,
            $message . ' updated',
            [
                'parseMode' => $this->textFormat,
            ]
        );

        $message->setReplyMarkup($replyMarkup);

        return [$message];
    }

    public function actionRemove($message = null)
    {
        $chatId = $this->messageHandler->getMessage()->getChat()->getId();
        $userId = $this->messageHandler->getMessage()->getFrom()->getId();
        BotMessageFilter::removeWord($message, $chatId, $userId);
        $customKeyboard = BotMessageFilter::getKeyBoardList($chatId);
        $replyMarkup = new ReplyKeyboardMarkup($customKeyboard, false, true);

        $message = new SendMessageCommand(
            $chatId,
            $message . ' updated',
            [
                'parseMode' => $this->textFormat,
            ]
        );

        $message->setReplyMarkup($replyMarkup);

        return [$message];
    }

    public function actionList()
    {
        $chatId = $this->messageHandler->getMessage()->getChat()->getId();

        $customKeyboard = BotMessageFilter::getKeyBoardList($chatId);
        $replyMarkup = new ReplyKeyboardMarkup($customKeyboard, false, true);

        $message = new SendMessageCommand(
            $chatId,
            $this->render('list'),
            [
                'parseMode' => $this->textFormat,
            ]
        );

        $message->setReplyMarkup($replyMarkup);

        return [$message];
    }


}
