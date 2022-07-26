<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\AdOffer;
use yii\web\NotFoundHttpException;

class AdOfferRepository
{
    public function findAdOfferByIdAndCurrentUser(int $id): AdOffer
    {
        if (
            $model = AdOffer::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()
        ) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findMatchedAdOfferByIdAndAdSearch(int $id, AdSearch $adSearch): AdOffer
    {
        if ($adOffer = $adSearch->getMatches()->where(['id' => $id])->one()) {
            return $adOffer;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
