<?php

declare(strict_types=1);

namespace app\models\scenarios\Resume;

use Yii;
use app\models\Resume;

final class SetActiveScenario
{
    private Resume $model;
    private array $errors = [];

    public function __construct(Resume $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateLanguages() && $this->validateLocation() && $this->validateRadius()) {
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
        if (!$this->model->getLanguages()->count()) {
            $this->errors['languages'] = Yii::t('app', 'At least one language should be set in your account') . '.';

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

    private function validateRadius(): bool
    {
        if (!$this->model->isRemote()) {
            if (!$this->model->search_radius) {
                $this->errors['location'] = Yii::t('app', 'Search Radius should be set when Location is active') . '.';

                return false;
            }
        }

        return true;
    }
}
