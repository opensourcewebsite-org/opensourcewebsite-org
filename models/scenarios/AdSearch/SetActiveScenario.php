<?php

declare(strict_types=1);

namespace app\models\scenarios\AdSearch;

use app\models\AdSearch;
use app\models\scenarios\traits\ValidateRatingTrait;
use Yii;

final class SetActiveScenario
{
    use ValidateRatingTrait;

    private AdSearch $model;

    private $modelClass = 'AdSearch';

    private array $errors = [];

    public function __construct(AdSearch $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateRating() && $this->validateLocation()) {
            $this->model->setActive();

            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        return $this->errors ? array_shift($this->errors) : '';
    }

    private function validateLocation(): bool
    {
        if (!($this->model->location_lon && $this->model->location_lat)) {
            $this->errors['location'] = Yii::t('app', 'Location should be set') . '.';

            return false;
        }

        return true;
    }
}
