<?php
declare(strict_types=1);

namespace app\models\matchers;

use yii\db\ActiveRecord;

abstract class BaseMatcher {

    abstract function match();

    public function clearMatches()
    {
        $this->unlinkMatches();

        $this->model->processed_at = null;
        $this->model->save();
    }

    protected function unlinkMatches()
    {
        $this->model->unlinkAll('matches', true);
        $this->model->unlinkAll('counterMatches', true);
    }

    /**
     * @template T
     * @param array<T> $matches
     */
    protected function linkMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('matches', $model);
        }
    }

    /**
     * @template T
     * @param array<T> $matches
     */
    protected function linkCounterMatches(array $matches)
    {
        foreach ($matches as $model) {
            $this->model->link('counterMatches', $model);
        }
    }
}
