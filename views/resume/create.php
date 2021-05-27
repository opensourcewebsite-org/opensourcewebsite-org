<?php
declare(strict_types=1);

use yii\web\View;
use app\models\Resume;
use app\models\Currency;

/* @var $this View */
/* @var $model Resume */
/* @var $currencies Currency[] */

$this->title = Yii::t('app', 'Create Resume');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resume'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="resume-create">

    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>

</div>
