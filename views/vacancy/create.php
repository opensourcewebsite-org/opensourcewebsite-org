<?php
declare(strict_types=1);

use app\models\Vacancy;
use yii\web\View;
use app\models\Currency;

/* @var $this View */
/* @var $model Vacancy */
/* @var $currencies Currency[] */

$this->title = Yii::t('app', 'Create Vacancy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vacancies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="vacancy-create">

    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>

</div>
