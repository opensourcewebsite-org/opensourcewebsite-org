<?php
declare(strict_types=1);

use yii\web\View;
use app\models\Company;

/**
 * @var View $this
 * @var Company $model
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Create Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="company-create">

    <?= $this->render('_form', $_params_); ?>

</div>
