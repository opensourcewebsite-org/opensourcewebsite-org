<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Rating;
use app\models\User;
use app\models\Debt;
use app\models\DebtBalance;
use yii\helpers\VarDumper;
use yii\db\Query;
use app\helpers\Number;

/**
 * Class CoreController
 *
 * @package app\commands
 */
class CoreController extends Controller
{
    public function actionIndex()
    {
        $this->actionCheckUserRatings();
        $this->actionCheckDebtBalances();
        $this->actionCheckDebtUniqueGroups();
    }

    public function actionCheckUserRatings()
    {
        echo 'START: ' . __METHOD__ . "\n";
        echo 'CHECK: Data collision between `rating` and `user` tables.' . "\n";

        $totalRatingByRatings = Rating::getTotalRating();
        $totalRatingByUsers = User::getTotalRating();
        $usersCount = User::find()->count();
        $usersDefaultRating = $usersCount * Rating::DEFAULT;

        if (($totalRatingByRatings + $usersDefaultRating) != $totalRatingByUsers) {
            echo 'ALERT: Total Rating by `rating` table ' . $totalRatingByRatings . ' + Users Default Rating ' . $usersDefaultRating . ' != Total Rating by `user` table ' . $totalRatingByUsers . "\n";
            echo 'UPDATING: `user.rating`.' . "\n";

            $users = User::find()
                ->all();

            foreach ($users as $user) {
                $user->updateRating();
            }

            echo 'UPDATED: `user.rating`.' . "\n";

            $totalRatingByRatings = Rating::getTotalRating();
            $totalRatingByUsers = User::getTotalRating();
            $usersCount = User::find()->count();
            $usersDefaultRating = $usersCount * Rating::DEFAULT;

            if (($totalRatingByRatings + $usersDefaultRating) == $totalRatingByUsers) {
                echo 'FIXED: Total Rating by `rating` table ' . $totalRatingByRatings . ' + Users Default Rating ' . $usersDefaultRating . ' == Total Rating by `user` table ' . $totalRatingByUsers . "\n";
            } else {
                echo 'ERROR: Failed to fix.' . "\n";
            }
        }

        echo 'FINISH: ' . __METHOD__ .  "\n";
    }

    /**
     * Check that there are no data collision between DB tables `debt` and `debt_balance`.
     * Should be valid next formula: `debt_balance.amount = sumOfAllDebt(Credits) - sumOfAllDebt(Deposits)`
     *
     * @throws \yii\db\Exception
     */
    public function actionCheckDebtBalances()
    {
        echo 'START: ' . __METHOD__ . "\n";
        echo 'CHECK: Data collision between `debt` and `debt_balance` tables.' . "\n";

        $query = (new Query())
            ->select([
               'd1.currency_id',
               'd1.from_user_id',
               'd1.to_user_id',
               'amount_sum' => 'SUM(d1.amount)',
            ])
            ->from(['d1' => Debt::tableName()])
            ->andWhere([
                'd1.status' => Debt::STATUS_CONFIRM,
            ])
            ->groupBy([
               'd1.currency_id',
               'd1.from_user_id',
               'd1.to_user_id',
            ]);

        $data = [];
        $rows = $query->all();

        foreach ($rows as $key => $row) {
            if ($row['from_user_id'] < $row['to_user_id']) {
                $data[$row['currency_id']][$row['from_user_id']][$row['to_user_id']]['credit'] = $row['amount_sum'];
            } else {
                $data[$row['currency_id']][$row['to_user_id']][$row['from_user_id']]['deposit'] = $row['amount_sum'];
            }
        }

        if (empty($data)) {
            return null;
        }

        foreach ($data as $currency_id => $users) {
            foreach ($users as $from_user_id => $debts) {
                foreach ($debts as $to_user_id => $debtSummary) {
                    $debtSummary['credit'] = $debtSummary['credit'] ?? 0;
                    $debtSummary['deposit'] = $debtSummary['deposit'] ?? 0;

                    $amountSumDiff = Number::floatSub($debtSummary['credit'], $debtSummary['deposit']);

                    $debtBalance = DebtBalance::find()
                        ->andWhere([
                            'from_user_id' => $from_user_id,
                            'to_user_id' => $to_user_id,
                            'currency_id' => $currency_id,
                        ])
                        ->one();

                    if (!$debtBalance) {
                        $debtBalance = new DebtBalance();

                        $debtBalance->setAttributes([
                            'from_user_id' => $from_user_id,
                            'to_user_id' => $to_user_id,
                            'amount' => $amountSumDiff,
                            'currency_id' => $currency_id,
                        ]);

                        echo 'ALERT: DebtBalance not found. Users IDs: ' . $debtBalance->from_user_id . ' > ' . $debtBalance->to_user_id . '. Amount: ' . $debtBalance->amount . ' ' . $debtBalance->currency->code . "\n";

                        if ($debtBalance->save()) {
                            echo 'CREATED: DebtBalance.' . "\n";
                        } else {
                            echo 'ERROR: Failed to create a DebtBalance.' . "\n";
                        }
                    } elseif (!Number::isFloatEqual($debtBalance->amount, $amountSumDiff)) {
                        echo 'ALERT: DebtBalance amount: ' . $debtBalance->amount . ' != Debt sum: ' . $amountSumDiff . ' ' . $debtBalance->currency->code . '. Users IDs: ' . $debtBalance->from_user_id . ' > ' . $debtBalance->to_user_id . "\n";

                        Yii::$app->db->beginTransaction();

                        if (!$debtBalance->isFoundForUpdate()) {
                            $debtBalance = DebtBalance::findOneForUpdate($debtBalance);
                        }

                        $debtBalance->amount = $amountSumDiff;

                        if ($debtBalance->save()) {
                            Yii::$app->db->getTransaction()->commit();

                            echo 'FIXED: DebtBalance.' . "\n";
                        } else {
                            Yii::$app->db->getTransaction()->rollBack();

                            echo 'ERROR: Failed to fix a DebtBalance.' . "\n";
                        }
                    }

                    $counterDebtBalance = DebtBalance::find()
                        ->andWhere([
                            'from_user_id' => $to_user_id,
                            'to_user_id' => $from_user_id,
                            'currency_id' => $currency_id,
                        ])
                        ->one();

                    if (!$counterDebtBalance) {
                        $counterDebtBalance = new DebtBalance();

                        $counterDebtBalance->setAttributes([
                            'from_user_id' => $to_user_id,
                            'to_user_id' => $from_user_id,
                            'amount' => -$amountSumDiff,
                            'currency_id' => $currency_id,
                        ]);

                        echo 'ALERT: counter DebtBalance not found. Users IDs: ' . $counterDebtBalance->from_user_id . ' > ' . $counterDebtBalance->to_user_id . '. Amount: ' . $counterDebtBalance->amount . ' ' . $counterDebtBalance->currency->code . "\n";

                        if ($counterDebtBalance->save()) {
                            echo 'CREATED: counter DebtBalance.' . "\n";
                        } else {
                            echo 'ERROR: Failed to create a counter DebtBalance.' . "\n";
                        }
                    } elseif (!Number::isFloatEqual($counterDebtBalance->amount, -$amountSumDiff)) {
                        echo 'ALERT: counter DebtBalance amount: ' . $counterDebtBalance->amount . ' != Debt sum: ' . -$amountSumDiff . ' ' . $counterDebtBalance->currency->code . '. Users IDs: ' . $counterDebtBalance->from_user_id . ' > ' . $counterDebtBalance->to_user_id . "\n";

                        Yii::$app->db->beginTransaction();

                        if (!$counterDebtBalance->isFoundForUpdate()) {
                            $counterDebtBalance = DebtBalance::findOneForUpdate($counterDebtBalance);
                        }

                        $counterDebtBalance->amount = -$amountSumDiff;

                        if ($counterDebtBalance->save()) {
                            Yii::$app->db->getTransaction()->commit();

                            echo 'FIXED: counter DebtBalance.' . "\n";
                        } else {
                            Yii::$app->db->getTransaction()->rollBack();

                            echo 'ERROR: Failed to fix a counter DebtBalance.' . "\n";
                        }
                    }
                }
            }
        }

        echo 'FINISH: ' . __METHOD__ . "\n";
    }

    public function actionCheckDebtUniqueGroups()
    {
        echo 'START: ' . __METHOD__ . "\n";
        echo 'CHECK: Duplicated users in same generated group of debts.' . "\n";

        $query = (new Query())
            ->select([
               'd1.id',
            ])
            ->from(['d1' => Debt::tableName()])
            ->leftJoin(['d2' => Debt::tableName()], 'd1.from_user_id IN (d2.from_user_id, d2.to_user_id)
                           AND d1.to_user_id IN (d2.from_user_id, d2.to_user_id)
                           AND d1.id <> d2.id
                           AND d1.`group` = d2.`group`')
            ->andWhere([
                'not', ['d1.group' => null],
            ]);

        if ($errors = $query->column()) {
            echo 'ALERT: found ' . count($errors) . ' invalid debts! Their IDs:' . "\n" . VarDumper::dumpAsString($errors) . "\n";
        }

        echo 'FINISH: ' . __METHOD__ . "\n";
    }
}
