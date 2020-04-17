<?php

namespace app\components\debt;

use app\models\Debt;
use app\models\DebtBalance;
use Yii;
use yii\base\Component;

class BalanceChecker extends Component
{
    /**
     * @throws \yii\db\Exception
     */
    public function run(): ?array
    {
        $sumOfAllDebt = $this->findSumOfAllDebt();

        if (empty($sumOfAllDebt)) {
            return null;
        }

        return $this->validate($sumOfAllDebt);
    }

    /**
     * @throws \yii\db\Exception
     */
    private function findSumOfAllDebt(): array
    {
        $sql = '
        SELECT debt.currency_id, debt.from_user_id, debt.to_user_id, SUM(debt.amount) as debt_sum, 
               debt_balance.amount as balance
        FROM debt
            LEFT JOIN debt_balance ON debt.currency_id  = debt_balance.currency_id
                                  AND debt.from_user_id = debt_balance.from_user_id
                                  AND debt.to_user_id   = debt_balance.to_user_id
        WHERE debt.status = :confirm
        GROUP BY debt.currency_id, debt.from_user_id, debt.to_user_id;';

        $data = [];
        $rows = Yii::$app->db->createCommand($sql, [':confirm' => Debt::STATUS_CONFIRM])->queryAll();

        foreach ($rows as $row) {
            $currencyId = $row['currency_id'];
            $fromUID = $row['from_user_id'];
            $toUID = $row['to_user_id'];

            $data[$currencyId][$fromUID][$toUID] = [
                'debt_sum' => $row['debt_sum'],
                'balance' => $row['balance'],
            ];
        }

        return $data;
    }

    private function validate(array $sumOfAllDebt): array
    {
        $errors            = [];
        $processedBalances = [];

        foreach ($sumOfAllDebt as $currencyId => $users) {
            foreach ($users as $fromUID => $debts) {
                foreach ($debts as $toUID => $debtSummary) {
                    if (isset($processedBalances["$currencyId:$toUID:$fromUID"])) {
                        continue; //no sense to analyze the same balance twice
                    }
                    $processedBalances["$currencyId:$fromUID:$toUID"] = true;

                    /**
                     * @var array $inverseDebtSummary
                     * E.g. if ($debtSummary is Deposit) then {$inverseDebtSummary is Credit}
                     */
                    $inverseDebtSummary = $sumOfAllDebt[$currencyId][$toUID][$fromUID] ?? null;
                    $debtSumDiff        = $debtSummary['debt_sum'] - ($inverseDebtSummary['debt_sum'] ?? 0);

                    $errorParams = [
                        '$currencyId'         => $currencyId,
                        '$fromUID'            => $fromUID,
                        '$toUID'              => $toUID,
                        '$debtSummary'        => $debtSummary,
                        '$inverseDebtSummary' => $inverseDebtSummary,
                    ];

                    if (!$inverseDebtSummary) {
                        $errors = $this->validateNoInverseDebt($errors, $errorParams, $debtSummary);
                    } elseif ($debtSumDiff == 0) {
                        $errors = $this->validateDiffIsEmpty($errors, $errorParams, $debtSummary, $inverseDebtSummary);
                    } elseif ($debtSumDiff < 0) {
                        $errors = $this->validateDiffIsNegative(
                            $errors,
                            $errorParams,
                            $debtSummary,
                            $inverseDebtSummary,
                            $debtSumDiff
                        );
                    } else { // ($debtSumDiff > 0)
                        $errors = $this->validateDiffIsPositive(
                            $errors,
                            $errorParams,
                            $debtSummary,
                            $inverseDebtSummary,
                            $debtSumDiff
                        );
                    }
                }
            }
        }

        return $errors;
    }

    private function isBalanceEmpty(array $debtSummary): bool
    {
        return DebtBalance::STORE_EMPTY_AMOUNT ? (0 == $debtSummary['balance']) : (null === $debtSummary['balance']);
    }

    private function validateNoInverseDebt($errors, $errorParams, $debtSummary): array
    {
        $valid = $debtSummary['debt_sum'] == $debtSummary['balance'];

        if (!$valid) {
            $errors[] = ['method' => __METHOD__] + $errorParams;
        }

        return $errors;
    }

    private function validateDiffIsEmpty($errors, $errorParams, $debtSummary, $inverseDebtSummary): array
    {
        $valid = $this->isBalanceEmpty($debtSummary) && $this->isBalanceEmpty($inverseDebtSummary);

        if (!$valid) {
            $errors[] = ['method' => __METHOD__] + $errorParams;
        }

        return $errors;
    }

    private function validateDiffIsNegative($errors, $errorParams, $debtSummary, $inverseDebtSummary, $debtSumDiff): array
    {
        $balanceIsEmpty   = $this->isBalanceEmpty($debtSummary);
        $diffEqualBalance = (abs($debtSumDiff) == $inverseDebtSummary['balance']);
        $valid            = $balanceIsEmpty && $diffEqualBalance;

        if (!$valid) {
            $errors[] = ['method' => __METHOD__] + $errorParams;
        }

        return $errors;
    }

    private function validateDiffIsPositive($errors, $errorParams, $debtSummary, $inverseDebtSummary, $debtSumDiff): array
    {
        $balanceIsEmpty   = $this->isBalanceEmpty($inverseDebtSummary);
        $diffEqualBalance = ($debtSumDiff == $debtSummary['balance']);
        $valid            = $balanceIsEmpty && $diffEqualBalance;

        if (!$valid) {
            $errors[] = ['method' => __METHOD__] + $errorParams;
        }

        return $errors;
    }
}
