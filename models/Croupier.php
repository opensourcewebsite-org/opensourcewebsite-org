<?php


namespace app\models;

/**
 * Used for calculating if one won the prize and its amount.
 *
 *
 * @package app\models
 */
class Croupier
{
    public const BET_MINIMUM_AMOUNT = 0.001; // XLM
    // minimum croupier balance that is reserved for account staying active
    public const BALANCE_RESERVE_AMOUNT = 5; // XLM
    // croupier balance percent reserved, so prize won't exceed (100 - self::PRIZE_RESERVE_PERCENT) % of the balance
    public const PRIZE_RESERVE_PERCENT = 20; // %
    public const PRIZE_RETURN_PERCENT = 95; // %
    public const WINNER_RATES = [2, 3, 4, 5, 10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000];

    /**
     * Prize amount based on bet amount, croupier balance, randomly chose win rate and randomly chose option
     * (has won or has lost)
     *
     * Returns array with scheme
     *
     * ```php
     * [
     *    'winner_rate' => int,
     *    'prize_amount' => float,
     * ]
     * ```
     *
     * If player lost, empty array returned
     *
     * @param float $betAmount
     * @param float $croupierBalance
     * @return array
     */
    public static function prizeAmount(float $betAmount, float $croupierBalance): array
    {
        if ($betAmount < self::BET_MINIMUM_AMOUNT) {
            return [];
        }

        $winnerRate = self::WINNER_RATES[array_rand(self::WINNER_RATES)];
        $winChance = (self::PRIZE_RETURN_PERCENT / 100) / $winnerRate;

        if (!self::generateBool($winChance)) {
            return [];
        }

        $prePrizeAmount = $betAmount * $winnerRate;
        $prizeAmount = min($prePrizeAmount, self::maximalPrizeAmount($croupierBalance));

        if ($prizeAmount < 0.000_000_1) {
            return [];
        }

        return [
            'winner_rate' => $winnerRate,
            'prize_amount' => $prizeAmount,
        ];
    }

    private static function generateBool(float $probability): bool
    {
        $rand = lcg_value(); // in range [0; 1]
        return $rand <= $probability;
    }

    private static function maximalPrizeAmount(float $balance): float
    {
        $balance -= self::BALANCE_RESERVE_AMOUNT;
        $balance *= (1 - (self::PRIZE_RESERVE_PERCENT / 100));
        if ($balance < 0.01) {
            $balance = 0;
        }
        return $balance;
    }
}
