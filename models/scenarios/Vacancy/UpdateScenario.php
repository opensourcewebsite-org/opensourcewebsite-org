<?php
declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use app\models\matchers\ModelLinker;
use app\models\Vacancy;

class UpdateScenario {

    private Vacancy $model;

    private ModelLinker $linker;

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
    }

    public function run(): bool
    {
        if ($this->model->isAttributeChanged('company_id') ||
            $this->model->isAttributeChanged('status') ||
            $this->model->isAttributeChanged('remote_on', false) ||
            $this->model->isAttributeChanged('max_hourly_rate') ||
            $this->model->isAttributeChanged('currency_id') ||
            $this->model->isAttributeChanged('gender_id') ||
            $this->model->isAttributeChanged('location_lat') ||
            $this->model->isAttributeChanged('location_lon')
        ){
            $this->linker->unlinkMatches();
            return true;
        }
        return false;
    }
}
