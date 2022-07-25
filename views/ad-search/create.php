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

$this->title = Yii::t('app', 'Create Search');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Searches'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-search-create">
    <?= $this->render('_form', $_params_); ?>
</div>
