<?php

declare(strict_types=1);

namespace app\models\scenarios\AdOffer;

use app\models\AdOffer;
use app\models\matchers\ModelLinker;

class UpdateScenario
{
    private AdOffer $model;

    private ModelLinker $linker;

    public function __construct(AdOffer $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
    }

    public function run(): bool
    {
        if ($this->model->isAttributeChanged('status') ||
            $this->model->isAttributeChanged('section') ||
            $this->model->isAttributeChanged('location_lat') ||
            $this->model->isAttributeChanged('location_lon') ||
            $this->model->isAttributeChanged('delivery_radius')
        ) {
            $this->linker->unlinkMatches();

            return true;
        }

        return false;
    }
}
