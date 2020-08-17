<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Rating;
use app\models\User;
use app\components\Converter;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyRatingController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyRatingController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->renderRating(),
                [
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => Emoji::DONATE . ' ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => Emoji::CONTRIBUTE . ' ' . Yii::t('bot', 'Contribute'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }

    private function renderRating()
    {
        $user = $this->getUser();

        $activeRating = $user->activeRating;

        $rating = $user->getRating();
        $totalRating = User::getTotalRating();
        $percent = $totalRating ? Converter::percentage($rating, $totalRating) : 0;

        $rank = $user->getRank();
        $totalRank = User::getTotalRank();

        $params = [
            'active_rating' => $activeRating,
            'overall_rating' => [$rating, $totalRating, $percent],
            'ranking' => [$rank, $totalRank],
        ];

        return $this->render('index', $params);
    }
}
