<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\models\Rating;
use \app\models\User;
use yii\db\Query;
use app\components\Converter;

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
        $user = $this->getUser();

        $activeRating = $user->activeRating;

        $rating = $user->rating;
        $totalRating = Rating::getTotalRating();
        if ($totalRating < 1) {
            $percent = 0;
        } else {
            $percent = Converter::percentage($rating, $totalRating);
        }

        $groupQuery = (new Query)
            ->select([
                'user_id',
                'balance' => '(sum(amount))',
            ])
            ->from(Rating::tableName() . ' r')
            ->innerJoin(User::tableName() . ' u ON u.id = r.user_id')
            ->groupBy('user_id')
            ->orderBy('balance DESC');

        $total = $groupQuery->count();

        $rank = (new Query)
            ->select(['count(*)+1'])
            ->from(['g' => $groupQuery])
            ->where(['>', 'balance', $model->rating])
            ->scalar();

        $params = [
            'active_rating' => $activeRating,
            'overall_rating' => [$rating, $totalRating, $percent],
            'ranking' => [$rank, $total],
        ];

        $text = $this->render('index', $params);

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                ])
            ),
        ];
    }
}
