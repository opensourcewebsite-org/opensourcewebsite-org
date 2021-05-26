<?php
declare(strict_types=1);

namespace app\models\scenarios\CompanyUser;

use app\models\Company;
use app\models\CompanyUser;
use app\models\Vacancy;

class DeleteCompanyScenario {

    private CompanyUser $companyUserModel;
    private array $errors = [];

    public function __construct(CompanyUser $companyUserModel) {
        $this->companyUserModel = $companyUserModel;
    }

    public function run(): bool
    {
        $numOfVacanciesOfCompany = Vacancy::find()->where(['company_id' => $this->companyUserModel->company_id])->count();

        if ($numOfVacanciesOfCompany > 0) {
            $this->errors[] = "Can't delete company, because company have open Vacancies. Delete Vacancies first!";
            return false;
        }

        $this->companyUserModel->delete();
        $this->companyUserModel->company->delete();

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
