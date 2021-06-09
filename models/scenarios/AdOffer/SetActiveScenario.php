<?php
declare(strict_types=1);

namespace app\models\scenarios\AdOffer;

use Yii;
use app\models\AdOffer;

final class SetActiveScenario
{
    private AdOffer $model;
    private array $errors = [];

    public function __construct(AdOffer $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateLocation()) {
            $this->model->setActive();
            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getModel(): AdOffer
    {
        return $this->model;
    }

    private function validateLocation(): bool
    {
        if (!($this->model->location_lon && $this->model->location_lat)) {
            $this->errors['location'] = Yii::t('app', 'Location should be set');
            return false;
        }

        return true;
    }
}
