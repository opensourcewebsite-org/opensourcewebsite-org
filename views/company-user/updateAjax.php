<?php

use app\models\Company;
use app\models\CompanyUser;
use yii\web\View;

/**
 * @var View $this
 * @var Company $companyModel
 * @var CompanyUser $companyUserModel
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Update Company') . " {$companyModel->name}";
?>

<?= $this->render('_form', $_params_) ?>
