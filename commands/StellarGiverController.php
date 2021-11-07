<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarGiver;
use app\models\User;
use app\models\UserStellar;
use app\models\Contact;
use DateTime;
use yii\console\Controller;

/**
 * Class StellarGiverController
 *
 * @package app\commands
 */
class StellarGiverController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->actionUpdateParticipants();
        //$this->sendBasicIncomes();
    }

    public function actionUpdateParticipants()
    {
        $updatesCount = 0;

        $users = User::find()
            ->where([
                'status' => User::STATUS_ACTIVE,
                'basic_income_on' => 1,
                'basic_income_processed_at' => null,
            ])
            ->joinWith('stellar')
            ->andWhere([
                'not',
                [UserStellar::tableName() . '.confirmed_at' => null],
            ])
            ->orderBy([
                'rating' => SORT_DESC,
                'created_at' => SORT_ASC,
            ])
            ->all();

        foreach ($users as $user) {
            $positiveRating = User::find()
                ->where([
                    'status' => User::STATUS_ACTIVE,
                ])
                ->joinWith('contacts')
                ->andWhere([
                    Contact::tableName() . '.link_user_id' => $user->id,
                    Contact::tableName() . '.is_basic_income_candidate' => 1,
                ])
                ->sum('rating');

            $negativeRating = User::find()
                ->where([
                    'status' => User::STATUS_ACTIVE,
                ])
                ->joinWith('contacts')
                ->andWhere([
                    Contact::tableName() . '.link_user_id' => $user->id,
                    Contact::tableName() . '.is_basic_income_candidate' => 2,
                ])
                ->sum('rating');

            $rating = $positiveRating - $negativeRating;

            if ($rating >= Yii::$app->settings->basic_income_min_rating_value_to_activate) {
                $user->confirmBasicIncomeActivatedAt();
            } else {
                $user->resetBasicIncomeActivatedAt();
            }

            $user->setAttributes([
                'basic_income_processed_at' => time(),
            ]);

            $user->save(false);

            $updatesCount++;
        }

        if ($updatesCount) {
            $this->output('Basic income participants/candidates processed: ' . $updatesCount);
        }
    }

    // TODO send basic incomes to participants
    public function sendBasicIncomes()
    {
        if ($stellarGiver = new StellarGiver()) {
            $today = new DateTime('today');

            if (!$stellarGiver->isPaymentDate($today)) {
                return;
            }
        }
    }
}
