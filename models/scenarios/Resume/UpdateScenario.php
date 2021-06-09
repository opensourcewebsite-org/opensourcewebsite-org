<?php
declare(strict_types=1);

namespace app\models\scenarios\Resume;

use app\models\matchers\ModelLinker;
use app\models\Resume;

class UpdateScenario
{
    private Resume $model;
    private ModelLinker $linker;

    public function __construct(Resume $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
    }

    public function run(): bool
    {
        if ($this->model->isAttributeChanged('status') ||
            $this->model->isAttributeChanged('remote_on') ||
            $this->model->isAttributeChanged('min_hourly_rate', false) ||
            $this->model->isAttributeChanged('search_radius') ||
            $this->model->isAttributeChanged('currency_id') ||
            $this->model->isAttributeChanged('location_lat') ||
            $this->model->isAttributeChanged('location_lon')
        ) {
            $this->linker->unlinkMatches();

            return true;
        }

        return false;
    }
}
