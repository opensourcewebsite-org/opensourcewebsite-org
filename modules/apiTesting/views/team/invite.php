<?php
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$this->title = 'Invite';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['/apiTesting/project/view', 'id' => $project->id]];
$this->params['breadcrumbs'][] = ['label' => 'Team', 'url' => ['index', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    $form = ActiveForm::begin(['fieldConfig' => [
        'options' => [
            'tag' => false,
        ],
    ]]);
?>
<div class="api-test-invite-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'user_id')->widget(Select2::class, [
                                'data' => ArrayHelper::map($users, 'id', 'displayName'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['index', 'id' => $project->id]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
