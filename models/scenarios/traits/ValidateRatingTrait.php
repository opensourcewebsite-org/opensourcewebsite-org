<?php

namespace app\models\scenarios\traits;

use Yii;

trait ValidateRatingTrait
{
    private array $settings = [
        'Vacancy' => [
            'active_vacancy_quantity_value_per_one_rating',
            'active_vacancy_min_quantity_value_per_one_user',
        ],
        'Resume' => [
            'active_resume_quantity_value_per_one_rating',
            'active_resume_min_quantity_value_per_one_user',
        ],
        'CurrencyExchangeOrder' => [
            'active_currency_exchange_order_quantity_value_per_one_rating',
            'active_currency_exchange_order_min_quantity_value_per_one_user',
        ],
        'AdOffer' => [
            'active_ad_offer_quantity_value_per_one_rating',
            'active_ad_offer_min_quantity_value_per_one_user',
        ],
        'AdSearch' => [
            'active_ad_search_quantity_value_per_one_rating',
            'active_ad_search_min_quantity_value_per_one_user',
        ],
    ];

    public function validateRating(): bool
    {
        $user = Yii::$app->user->identity;

        if (isset($this->settings[$this->modelClass])) {
            $activeModelsCount = $this->model::find()
                ->live()
                ->userOwner()
                ->count();

            $maxActiveModelsCount = (int)max(floor($user->getRating() * Yii::$app->settings->{$this->settings[$this->modelClass][0]}), Yii::$app->settings->{$this->settings[$this->modelClass][1]});

            if ($maxActiveModelsCount <= $activeModelsCount) {
                $requiredRating = (int)ceil(($activeModelsCount + 1) / Yii::$app->settings->{$this->settings[$this->modelClass][0]});

                $this->errors['rating'] = Yii::t('app', 'To activate this object please increase your Rating to at least {0}', $requiredRating) . '.';

                return false;
            }
        }

        return true;
    }
}
