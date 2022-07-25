<?php

declare(strict_types=1);

use app\models\AdSearch;
use yii\web\View;
use app\models\Currency;

/**
 * @var View $this
 * @var AdSearch $model
 * @var Currency[] $currencies
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Update Search') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Searches'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="ad-search-update">
    <?= $this->render('_form', $_params_); ?>
</div>
