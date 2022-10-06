<?php

declare(strict_types=1);

namespace app\models\matchers;

use Yii;
use yii\db\ActiveRecord;

class ModelLinker
{
    private ActiveRecord $model;

    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;
    }

    public function clearMatches()
    {
        $this->unlinkMatches();

        $this->model->processed_at = null;
        $this->model->save(false);
    }

    public function unlinkMatches()
    {
        $this->model->unlinkAll('matchModels', true);
        $this->model->unlinkAll('counterMatchModels', true);
    }

    /**
     * @param array $matches
     */
    public function linkMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('matchModels', $model);
        }
    }

    /**
     * @param array $matches
     */
    public function linkCounterMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('counterMatchModels', $model);
        }
    }

    /**
     * @param string $linkName
     * @param array $models
     */
    public function linkAll(string $linkName, array $models)
    {
        foreach ($models as $model) {
            $this->model->link($linkName, $model);
        }
    }
}
