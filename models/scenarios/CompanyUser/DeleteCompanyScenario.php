<?php
declare(strict_types=1);

namespace app\models\scenarios\CompanyUser;

use app\models\Company;
use app\models\CompanyUser;
use app\models\Vacancy;

class DeleteCompanyScenario
{
    private Company $companyModel;
    private array $errors = [];

    public function __construct(Company $companyModel) {
        $this->companyModel = $companyModel;
    }

    public function run(): bool
    {
        $numOfVacanciesOfCompany = Vacancy::find()->where(['company_id' => $this->companyModel->id])->count();

        if ($numOfVacanciesOfCompany > 0) {
            $this->errors[] = "Can't delete company, because Company have open Vacancies. Delete Vacancies first!";
            return false;
        }

        CompanyUser::deleteAll(['company_id' => $this->companyModel->id]);

        $this->companyModel->delete();

        return true;
    }

    public function getFirstError(): string
    {
        if ($ret = reset($this->errors)){
            return $ret;
        }

        return '';
    }
}
