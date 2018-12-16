<?php

use app\models\SupportGroupCommandText;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Nav;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupCommand */
/* @var $text app\models\SupportGroupCommandText */

$this->title = 'View command: ' . $model->command;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Commands', 'url' => ['commands', 'id' => $model->support_group_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-12">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-11">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="col-1 text-right">
                    <a class="btn btn-light" href="#" title="Edit" data-toggle="modal" data-target="#exampleModalLongEditCommand"><i class="fas fa-edit"></i></a>
                    <?php $form = ActiveForm::begin() ?>
                    <div class="modal fade" id="exampleModalLongEditCommand" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle">Edit command: <?= $model->command ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-left">
                                    <?= $form->field($model, 'command')->textInput(['maxlength' => true]) ?>
                                    <?= $form->field($model, 'is_default')->checkbox() ?>
                                </div>
                                <div class="card-footer text-left">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a class="btn btn-secondary" href="#" data-dismiss="modal">Cancel</a>
                                    <a class="btn btn-danger float-right" href="command-delete?id=<?= $model->id ?>" onclick="#">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-condensed">
                    <tbody>
                    <tr>
                        <td>
                            <?= Nav::widget([
                                'options' => ['class' => 'nav ml-auto p-2 flex-column'],
                                'items' => array_merge(['<li><h4>Languages</h4></li>'], $model->getNavItems($text))
                            ]); ?>
                        </td>
                        <td>
                            <div class="card-body">
                                <div class="tab-content">
                                    <?php foreach ($model->getLanguage() as $i => $lang) { ?>
                                        <div class="tab-pane <?= $i == 0 ? 'active show' : '' ?>" id="tab_<?= $lang->id ?>">

                                            <?= isset($text[$lang->language_code]) ? $text[$lang->language_code]['text'] : '' ?>

                                            <div class="text-right">
                                                <a class="btn btn-light" id="bottonModal<?= $lang->id ?>" href="#" title="Edit" data-toggle="modal"
                                                   data-target="#exampleModalLong<?= $lang->id ?>"><i class="fas fa-edit"></i></a>
                                            </div>
                                            <?php $form = ActiveForm::begin(['action' => 'text-update?id=' . 2]) ?>
                                            <div class="modal fade" id="exampleModalLong<?= $lang->id ?>" tabindex="-1" role="dialog"
                                                 aria-labelledby="exampleModalLongTitle" style="display: none;" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLongTitle">Edit <?= $model->command ?>: <?= $lang->languageCode->name_ascii ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            <?= $form->field(new SupportGroupCommandText(), 'text')->textarea(['value' => isset($text[$lang->language_code]) ? $text[$lang->language_code]['text'] : '', 'rows' => 3]) ?>
                                                            <?= $form->field(new SupportGroupCommandText(), 'language_code')->hiddenInput(['value' => $lang->language_code])->label(false) ?>
                                                            <?= $form->field(new SupportGroupCommandText(), 'support_group_command_id')->hiddenInput(['value' => $model->id])->label(false) ?>
                                                        </div>
                                                        <div class="card-footer text-left">
                                                            <button type="submit" class="btn btn-success">Save</button>
                                                            <a class="btn btn-secondary" href="#" data-dismiss="modal" title="Cancel">Cancel</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php ActiveForm::end(); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
            </div>
        </div>
    </div>
</div>

<?php /*= Html::a('Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
        'confirm' => 'Are you sure you want to delete this item?',
        'method' => 'post',
    ],
]) */?>
