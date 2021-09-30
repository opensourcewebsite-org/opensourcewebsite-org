<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\matchers\ModelLinker;
use app\models\User;
use app\models\Rating;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class RatingFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        $type = $this->faker->randomElement([
            Rating::TEAM,
            Rating::DONATE,
        ]);
        $amount = $this->faker->numberBetween(1, 9);

        $user->addRating($type, $amount);

        return $user;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ?ActiveRecord
    {
        return $this->factoryModel();
    }
}
