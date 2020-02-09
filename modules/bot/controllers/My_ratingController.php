<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\models\Rating;
use \app\models\User;
use \app\components\Converter;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;

/**
 * Class My_ratingController
 *
 * @package app\modules\bot\controllers
 */
class My_ratingController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();
        
        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->renderRating(),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'text' => Yii::t('bot', 'Update'),
                                'callback_data' => '/update_rating'
                            ]
                        ]
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->renderRating(),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'text' => Yii::t('bot', 'Refresh'),
                                'callback_data' => '/update_rating'
                            ]
                        ]
                    ]),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }

    private function renderRating()
    {
        $user = $this->getUser();

        $activeRating = $user->activeRating;

        $rating = $user->rating;
        $totalRating = Rating::getTotalRating();
        if ($totalRating < 1) {
            $percent = 0;
        } else {
            $percent = Converter::percentage($rating, $totalRating);
        }

        list($total, $rank) = Rating::getRank($rating);

        $params = [
            'active_rating' => $activeRating,
            'overall_rating' => [$rating, $totalRating, $percent],
            'ranking' => [$rank, $total],
        ];

        return $this->render('index', $params);
    }
}
