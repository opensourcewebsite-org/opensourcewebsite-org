<?php

use yii\widgets\ActiveForm;
use app\widgets\buttons\AddButton;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $member app\models\SupportGroupMember */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-md-12">
    <?php $this->beginBlock('content-header-data'); ?>
        <div class="row mb-2">
            <div class="col-sm-4">
                <h1 class="text-dark mt-4"><?= Html::encode($this->title) ?></h1>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <div class="alert alert-info" role="alert">
                    <b>Support Group Members:</b> <?= Yii::$app->user->identity->supportGroupMemberCount ?>/<?= Yii::$app->user->identity->maxSupportGroupMember ?>.
                    (<?= $settingQty ?> per 1 User Rating)
                </div>
            </div>
        </div>
    <?php $this->endBlock(); ?>
    <div class="card">
        <div class="card-header text-right">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]) ?>
            <?= AddButton::widget([
                'url' => '#',
                'options' => [
                    'title' => 'New Member',
                    'data-toggle' => 'modal',
                    'data-target' => '#exampleModalLong',
                ]
            ]); ?>
            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Add member</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-left">
                            <?php echo $form->field($member, 'user_id')->widget(Select2::class, [
                                'data' => ArrayHelper::map($user, 'id', 'displayName'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ])->label('User'); ?>
                        </div>
                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'options' => ['class' => 'card-body p-0'],
            'columns' => [
                [
                    'label' => 'User',
                    'attribute' => 'user_id',
                    'value' => function ($data) {
                        $name = $data->user_id;
                        if (!empty($data->user->contact)) {
                            $name = $data->user->getDisplayName();
                        }
                        return $name;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return ButtonDropdown::widget([
                                'encodeLabel' => false,
                                'label' => '<i class="fas fa-cog"></i>',
                                'dropdown' => [
                                    'items' => [
                                        [
                                            'label' => Yii::t('app', 'Remove'),
                                            'url' => ['members-delete', 'id' => $key],
                                            'linkOptions' => ['class' => 'dropdown-item']
                                        ],
                                    ],
                                ],
                                'options' => [
                                    'class' => 'btn-default',
                                ],
                            ]);
                        },
                    ],
                ],
            ]
        ]); ?>
        <div class="card-footer clearfix">
        </div>
    </div>
</div>
