<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarOperator;
use app\models\UserStellarDepositIncome;
use DateTime;
use yii\console\Controller;

/**
 * Class StellarOperatorController
 *
 * @package app\commands
 */
class StellarOperatorController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->actionSendDepositIncomes();
    }

    public function actionSendDepositIncomes()
    {
        if ($stellarOperator = new StellarOperator()) {
            $today = new DateTime('today');

            if (!$stellarOperator->isPaymentDate($today)) {
                return;
            }

            $processedAt = time();

            foreach (StellarOperator::MINIMUM_BALANCES as $assetCode => $minimumBalance) {
                if (!StellarOperator::incomesSentAlready($assetCode, $today)) {
                    // Delete all unfinished incomes data
                    StellarOperator::deleteIncomesDataFromDatabase($assetCode, $today);
                    // Collect and save recipients
                    $stellarOperator->fetchAndSaveRecipients($assetCode, $minimumBalance);
                }
                // Send incomes to recipients
                $stellarOperator->sendIncomeToRecipients($assetCode, $today);

                $processedAccountsCount = UserStellarDepositIncome::find()
                    ->andWhere([
                        'asset_code' => $assetCode,
                    ])
                    ->andWhere([
                        '>=', 'processed_at', $processedAt,
                    ])
                    ->count();

                if ($processedAccountsCount) {
                    $paidAccountsCount = UserStellarDepositIncome::find()
                        ->andWhere([
                            'asset_code' => $assetCode,
                        ])
                        ->andWhere([
                            '>=', 'processed_at', $processedAt,
                        ])
                        ->andWhere([
                            'result_code' => null,
                        ])
                        ->count();

                    if ($paidAccountsCount) {
                        $paidIncomes = UserStellarDepositIncome::find()
                            ->andWhere([
                                'asset_code' => $assetCode,
                            ])
                            ->andWhere([
                                '>=', 'processed_at', $processedAt,
                            ])
                            ->andWhere([
                                'result_code' => null,
                            ])
                            ->sum('income');
                    } else {
                        $paidIncomes = 0;
                    }

                    $failedAccountsCount = UserStellarDepositIncome::find()
                        ->andWhere([
                            'asset_code' => $assetCode,
                        ])
                        ->andWhere([
                            '>=', 'processed_at', $processedAt,
                        ])
                        ->andWhere([
                            'not',
                            ['result_code' => null],
                        ])
                        ->count();

                    $this->output('Accounts processed: ' . $processedAccountsCount . '.'
                        . ($paidAccountsCount ? ' Accounts paid: ' . $paidAccountsCount . '.' : '')
                        . ($paidIncomes ? ' Paid: ' . $paidIncomes . ' ' . $assetCode . '.' : '')
                        . ($failedAccountsCount ? ' Accounts failed: ' . $failedAccountsCount . '.' : ''));
                }
            }

            if (!$stellarOperator->getRecipients($today)) {
                $stellarOperator->setNextPaymentDate();

                $this->output('Next Payment Date: ' . $stellarOperator->getNextPaymentDate());
            }
        }
    }
}
