<?php

declare(strict_types=1);

namespace app\repositories;

use yii\web\NotFoundHttpException;

use app\models\AdSearch;

class AdSearchRepository
{
    public function findAdSearchByIdAndCurrentUser(int $id): AdSearch
    {
        if (
            $model = AdSearch::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()
        ) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findMatchedAdSearchByIdAndAdOrder(int $id, AdOffer $adOffer): AdSearch
    {
        if ($adSearch = $adOffer->getMatches()->where(['id' => $id])->one()) {
            return $adSearch;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
