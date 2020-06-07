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
        echo "Checking: Ratings\n";

        $totalRatingByRatings = Rating::getTotalRating();
        $totalRatingByUsers = User::getTotalRating();

        if ($totalRatingByRatings != $totalRatingByUsers) {
            echo "TotalRatingByRatings $totalRatingByRatings != TotalRatingByUsers $totalRatingByUsers\n";
            echo "Updating user ratings...\n";

            $users = User::find()->where(['is_authenticated' => 1])->all();
            foreach ($users as $user) {
                $user->updateRating();
            }

            echo "Updated user ratings...\n";
        }

        echo "Checked: Ratings\n";
    }
}
