<?php

declare(strict_types=1);

use app\models\AdOffer;
use yii\web\View;
use app\models\Currency;

/**
 * @var View $this
 * @var AdOffer $model
 * @var Currency[] $currencies
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Update Offer') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Offers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="ad-offer-update">
    <?= $this->render('_form', $_params_); ?>
</div>
