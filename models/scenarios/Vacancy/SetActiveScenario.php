<?php

declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use Yii;
use app\models\Vacancy;
use app\models\scenarios\traits\ValidateRatingTrait;

final class SetActiveScenario
{
    use ValidateRatingTrait;

    private Vacancy $model;
    private $modelClass = 'Vacancy';
    private array $errors = [];

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateRating() && $this->validateLanguages() && $this->validateLocation()) {
            $this->model->setActive();

            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateLanguages(): bool
    {
        if (!$this->model->getLanguagesWithLevels()->count()) {
            $this->errors['languages'] = Yii::t('app', 'At least one language should be set') . '.';

            return false;
        }

        return true;
    }

    private function validateLocation(): bool
    {
        if (!$this->model->isRemote()) {
            if (!($this->model->location_lon && $this->model->location_lat)) {
                $this->errors['location'] = Yii::t('app', 'Location should be set when Offline work is active') . '.';

                return false;
            }
        }

        return true;
    }
}
