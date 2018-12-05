<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $member app\models\SupportGroupMember */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-md-12">
    <div class="card">
        <div class="card-header text-right">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]) ?>
            <a class="btn btn-success ml-3" href="#" title="New Member"  data-toggle="modal" data-target="#exampleModalLong">New Member</a>
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
                            <?php echo $form->field($member, 'user_id')->textInput(['maxlength' => true]) ?>
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
                'user_id',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' => ['class' => 'text-right'],
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            $url = Url::to(['members-delete', 'id' => $model->id]);
                            return Html::a('<i class="fas fa-trash"></i>', $url, ['class' => 'btn trash btn-default']);
                        },
                    ],
                ],
            ]
        ]); ?>
        <div class="card-footer clearfix">
        </div>
    </div>
</div>