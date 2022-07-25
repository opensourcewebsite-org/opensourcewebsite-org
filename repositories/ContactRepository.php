<?php

declare(strict_types=1);

namespace app\repositories;

use yii\web\NotFoundHttpException;

use app\models\Contact;

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
