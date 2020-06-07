<?php
use app\assets\AdminLteContributingAsset;
use app\components\helpers\ArrayHelper;
use app\modules\apiTesting\models\ApiTestProject;
use app\modules\apiTesting\models\GraphFilterForm;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $totalForAllTime int */
/* @var $successfulForAllTime int */
/* @var $failedForAllTime int */
/* @var $successRatio int */
/* @var $yearBegin DateTime */
/* @var $monthBegin DateTime */
/* @var $weekBegin DateTime */
/* @var $yearEnd DateTime */
/* @var $monthEnd DateTime */
/* @var $weekEnd DateTime */
/* @var $filterModel GraphFilterForm */
$this->registerAssetBundle(AdminLteContributingAsset::class);

$formatter = Yii::$app->formatter;
$this->title = 'Graphs';
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
 ?>
<?=$this->render('_tabs', [
     'project' => $project
 ]); ?>

<div class="card">
    <div class="card-header">
        <h2>Overall statistic</h2>
        <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col-md-3">
                    <?=$form->field($filterModel, 'id')
                        ->dropDownList(ArrayHelper::map(ApiTestProject::find()->my()->all(), 'id', 'name'), [
                            'prompt' => 'Select project'
                        ])->label(false); ?>
                </div>
                <div class="col-md-3">
                    <?=$form->field($filterModel, 'server_id')
                        ->dropDownList(ArrayHelper::map($project->servers, 'id', 'fullAddress'), [
                            'prompt' => 'Select server..'
                        ])->label(false); ?>
                </div>
                <div class="col-md-1">
                    <?= SaveButton::widget([
                        'text' => 'Apply'
                    ]); ?>
                </div>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
    <div class="card-body">

        Total: <strong><?= $totalForAllTime; ?></strong>
        <br>
        Success <strong><?= $successfulForAllTime; ?></strong>
        <br>
        Failed <strong><?= $failedForAllTime; ?></strong>
        <br>
        Success ratio: <strong><?= Yii::$app->formatter->asDecimal($successRatio, '2'); ?>%</strong>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h2>Last week</h2>
        <span>(<?=$formatter->asDate($weekBegin->getTimestamp(), 'long'); ?> - <?=$formatter->asDate($weekEnd->getTimestamp(), 'long'); ?>)</span>
    </div>
    <div class="card-body">
        <i class="fas fa-circle" style="color: #81c784;"></i> Success
        &nbsp;
        <i class="fas fa-circle" style="color: #bdbdbd;"></i> Failed

        <div id="line-chart" style="width:100%; height:200px;" class="mt-4">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Last month</h2>
        <span>(<?=$formatter->asDate($monthBegin->getTimestamp(), 'long'); ?> - <?=$formatter->asDate($monthEnd->getTimestamp(), 'long'); ?>)</span>
    </div>
    <div class="card-body">
        <i class="fas fa-circle" style="color: #81c784;"></i> Success
        &nbsp;
        <i class="fas fa-circle" style="color: #bdbdbd;"></i> Failed

        <div id="last-month-graph" style="width:100%; height:200px;" class="mt-4">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Last year</h2>
        <span>(<?=$formatter->asDate($yearBegin->getTimestamp(), 'long'); ?> - <?=$formatter->asDate($yearEnd->getTimestamp(), 'long'); ?>)</span>

    </div>
    <div class="card-body">
        <i class="fas fa-circle" style="color: #81c784;"></i> Success
        <i class="fas fa-circle" style="color: #bdbdbd;"></i> Failed
        <div id="last-year-graph" style="width:100%; height:200px;" class="mt-4">
        </div>
    </div>
</div>

<?php
$this->registerJs("
var line_data1 = {
    label: 'Success',
    data : ".json_encode($lastWeekDataset[0]).",
    color: '#81c784'
};
var line_data2 = {
    label: 'Failed',
    data : ".json_encode($lastWeekDataset[1]).",
    color: '#bdbdbd'
};
$.plot('#line-chart', [line_data1, line_data2], {
            grid : {
                hoverable : true,
                borderColor: '#f3f3f3',
                borderWidth: 1,
                tickColor : '#f3f3f3'
            },
            series: {
                shadowSize: 0,
                lines : {
                    show: true
                },
                points : {
                    show: true
                }
            },
            lines : {
                fill : false,
                color: ['#3c8dbc', '#f56954']
            },
            yaxis : {
                show: true,
                minTickSize: 1,
            },
            xaxis : {
                mode: 'time',
                timeformat: \"%y/%m/%d\",
                minTickSize: [1, \"day\"]
            }
        });
");

$this->registerJs("
var line_data1 = {
    label: 'Success',
    data : ".json_encode($lastMonthDataset[0]).",
    color: '#81c784'
};
var line_data2 = {
    label: 'Failed',
    data : ".json_encode($lastMonthDataset[1]).",
    color: '#bdbdbd'
};
$.plot('#last-month-graph', [line_data1, line_data2], {
            grid : {
                hoverable : true,
                borderColor: '#f3f3f3',
                borderWidth: 1,
                tickColor : '#f3f3f3'
            },
            series: {
                shadowSize: 0,
                lines : {
                    show: true
                },
                points : {
                    show: true
                }
            },
            lines : {
                fill : false,
                color: ['#3c8dbc', '#f56954']
            },
            yaxis : {
                show: true,
                minTickSize: 1,
            },
            xaxis : {
                mode: 'time',
                timeformat: \"%y/%m/%d\",
                minTickSize: [1, \"day\"]
            }
        });
");

$this->registerJs("
var line_data1 = {
    label: 'Success',
    data : ".json_encode($lastYearDataset[0]).",
    color: '#81c784'
};
var line_data2 = {
    label: 'Failed',
    data : ".json_encode($lastYearDataset[1]).",
    color: '#bdbdbd'
};

$.plot('#last-year-graph', [line_data1, line_data2], {
            grid : {
                hoverable : true,
                borderColor: '#f3f3f3',
                borderWidth: 1,
                tickColor : '#f3f3f3'
            },
            series: {
                shadowSize: 0,
                lines : {
                    show: true
                },
                points : {
                    show: true
                }
            },
            lines : {
                fill : false,
                color: ['#3c8dbc', '#f56954']
            },
            yaxis : {
                show: true,
                minTickSize: 1,
            },
            xaxis : {
                mode: 'time',
                timeformat: \"%y/%m\",
                minTickSize: [1, \"month\"]
            }
        });
");
