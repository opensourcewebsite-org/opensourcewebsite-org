<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Rating;
use app\models\User;

/**
 * Class CoreController
 *
 * @package app\commands
 */
class CoreController extends Controller
{
    public function actionIndex()
    {
        $this->actionCheckRating();
    }

    public function actionCheckRating()
    {
        echo 'Checking: Ratings.' . "\n";

        $totalRatingByRatings = Rating::getTotalRating();
        $totalRatingByUsers = User::getTotalRating();
        $usersCount = User::find()->count();
        $usersDefaultRating = $usersCount * Rating::DEFAULT;

        if (($totalRatingByRatings + $usersDefaultRating) != $totalRatingByUsers) {
            echo 'Total Rating By Rating table ' . $totalRatingByRatings . ' + Users Default Rating ' . $usersDefaultRating . ' != Total Rating By User table ' . $totalRatingByUsers . "\n";
            echo 'Updating user ratings.' . "\n";

            $users = User::find()
                ->all();

            foreach ($users as $user) {
                $user->updateRating();
            }

            echo 'Updated user ratings.' . "\n";

            $totalRatingByRatings = Rating::getTotalRating();
            $totalRatingByUsers = User::getTotalRating();
            $usersCount = User::find()->count();
            $usersDefaultRating = $usersCount * Rating::DEFAULT;

            echo 'Total Rating By Rating table ' . $totalRatingByRatings . ' + Users Default Rating ' . $usersDefaultRating . ' != Total Rating By User table ' . $totalRatingByUsers . "\n";
        }

        echo 'Checked: Ratings.' . "\n";
    }
}
