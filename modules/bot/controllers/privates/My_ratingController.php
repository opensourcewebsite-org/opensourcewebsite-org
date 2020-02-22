<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\models\Rating;
use \app\components\Converter;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class My_ratingController
 *
 * @package app\modules\bot\controllers
 */
class My_ratingController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->renderRating(),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/help',
                                'text' => '🔙',
                            ],
                            [
                                'text' => Yii::t('bot', 'Donate'),
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md'
                            ],
                            [
                                'text' => Yii::t('bot', 'Contribution'),
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md'
                            ],
                        ],
                    ]),
                ]
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
