<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Contact;
use yii\web\NotFoundHttpException;

class ContactRepository
{
    public function findContact(int $id): ?Contact
    {
        $model = Contact::find()
            ->andWhere([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
