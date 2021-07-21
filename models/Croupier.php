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

    public static function prizeAmount(float $betAmount, float $croupierBalance): array
    {
        if ($betAmount < self::BET_MINIMUM_AMOUNT) {
            return [];
        }

        $winnerRate = self::WINNER_RATES[array_rand(self::WINNER_RATES)];
        $winChance = (self::PRIZE_RETURN_PERCENT / 100) / $winnerRate;

        if (self::generateBool($winChance)) {
            $prePrizeAmount = $betAmount * $winnerRate;
            return [
                'winner_rate' => $winnerRate,
                'prize_amount' => min($prePrizeAmount, self::maximalPrizeAmount($croupierBalance))
            ];
        } else {
            return [];
        }
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
