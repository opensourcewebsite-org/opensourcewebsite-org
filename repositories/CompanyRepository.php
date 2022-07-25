<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Company;
use app\models\CompanyUser;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyRepository
{
    public function findCompanyByIdAndCurrentUser(int $id): Company
    {
        $user = Yii::$app->user->identity;

        /** @var Company $company */
        if (
            $company = Company::find()
            ->joinWith('companyUser cu')
            ->where(['company.id' => $id])
            ->andWhere(['cu.user_id' => $user->id])
            ->andWhere(['cu.user_role' => CompanyUser::ROLE_OWNER])
            ->one()
        ) {
            return $company;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
