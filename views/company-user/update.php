<?php

use app\models\Company;
use app\models\CompanyUser;
use yii\web\View;

/**
 * @var View $this
 * @var CompanyUser $companyUserModel
 * @var Company $companyModel
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Update Company') . ' #' . $companyModel->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $companyUserModel->id, 'url' => ['view', 'id' => $companyUserModel->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="company-update">
    <?= $this->render('_form', $_params_); ?>
</div>
