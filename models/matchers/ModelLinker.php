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
        $this->model->save();
    }

    public function unlinkMatches()
    {
        $this->model->unlinkAll('matches', true);
        $this->model->unlinkAll('counterMatches', true);
    }

    /**
     * @template T
     * @param array<T> $matches
     */
    public function linkMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('matches', $model);
        }
    }

    /**
     * @template T
     * @param array<T> $matches
     */
    public function linkCounterMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('counterMatches', $model);
        }
    }

    /**
     * @template T
     * @param string $linkName
     * @param array<T> $models
     */
    public function linkAll(string $linkName, array $models)
    {
        foreach ($models as $model) {
            $this->model->link($linkName, $model);
        }
    }
}
