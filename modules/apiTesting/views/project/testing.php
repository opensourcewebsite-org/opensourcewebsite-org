<?php

use app\modules\apiTesting\models\ApiTestLabel;
use app\modules\apiTesting\models\ApiTestProject;
use app\modules\apiTesting\widgets\ProjectDropdownMenu;
use app\modules\apiTesting\widgets\ServerDropdownMenu;
use app\widgets\buttons\AddButton;
use yii\bootstrap4\Tabs;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $project ApiTestProject
 * @var $labels ApiTestLabel[]
 * @var $this View
 * @var $requestsDataProvidersByLabelId ActiveDataProvider[]
 */
$this->title = $project->name." testing main page";
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project/']];
$this->params['breadcrumbs'][] = $this->title;

$items = [];
$active = true;

foreach ($labels as $label) {
    $items[] = [
        'id' => 'label-tab'.$label->id,
        'active' => $active,
        'encode' => false,
        'label' => $label->name.' '.Html::tag('span', $requestsDataProvidersByLabelId[$label->id]->totalCount, ['class' => 'badge badge-primary']),
        'content' => $this->render('_requests_grid', [
            'dataProvider' => $requestsDataProvidersByLabelId[$label->id]
        ])
    ];
    $active = false;
}

?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?=Html::beginForm(['/apiTesting/project/testing', 'id' => $project->id], 'GET'); ?>
                <div class="input-group input-group">
                    <input type="text" class="form-control" name="q" value="<?=Yii::$app->request->get('q'); ?>">
                    <span class="input-group-append">
                            <button type="button" class="btn btn-info btn-flat">
                                <i class="fas fa-search"></i>
                            </button>
                        </span>
                </div>
                <?=Html::endForm(); ?>
            </div>
            <div class="offset-6">
            </div>
            <div class="col-md-1">
                <?= AddButton::widget([
                    'url' => ['/apiTesting/request/create', 'id' => $project->id],
                ]); ?>
            </div>
            <div class="col-md-1">
                <?= ProjectDropdownMenu::widget([
                    'project' => $project
                ]); ?>
            </div>

        </div>
    </div>
</div>

<?= Tabs::widget([
    'items' => $items
]); ?>

