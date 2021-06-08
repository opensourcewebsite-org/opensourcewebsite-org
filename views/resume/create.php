<?php
declare(strict_types=1);

use yii\web\View;
use app\models\Resume;
use app\models\Currency;

/**
 * @var View $this
 * @var Resume $model
 * @var Currency[] $currencies
 */

$this->title = Yii::t('app', 'Create Resume');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="resume-create">

    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>

</div>
