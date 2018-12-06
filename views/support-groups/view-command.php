<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\bootstrap\Nav;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupCommand */

$this->title = 'View command: ' . $model->command;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
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
                    <div class="modal fade" id="exampleModalLongEditCommand" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle">Edit command: /start</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-left">
                                    <p>Command</p>
                                    <input type="text" value="/start" class="form-control" >
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" value="option1">
                                        <label class="form-check-label">is default</label>
                                    </div>
                                </div>
                                <div class="card-footer text-left">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a class="btn btn-secondary" href="https://opensourcewebsite.org/moqup/design-view?id=47">Cancel</a>
                                    <a class="btn btn-danger float-right" href="#" onclick="#">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-condensed">
                    <tbody>
                    <tr>
                        <td>
                            <?php
                            $navItems = ['<li><h4>Languages</h4></li>'];
                            $langs = \app\models\SupportGroupLanguage::findAll(['support_group_id' => $model->support_group_id]);
                            foreach($langs as $lang) {
                                $url = '#tab_' . $lang->id;
                                $navItems[] = ['label' => $lang->languageCode->name_ascii, 'url' => $url, 'linkOptions' => ['data-toggle'=>'tab']];
                            }
                            ?>
                            <?= Nav::widget([
                                'options' => ['class' => 'nav ml-auto p-2 flex-column'],
                                'items' => $navItems
                            ]); ?>
                        </td>
                        <td>
                            <div class="card-body">
                                <div class="tab-content">
                                    <?php foreach ($langs as $i => $lang) { ?>
                                        <div class="tab-pane <?= $i == 0 ? 'active show' : '' ?>" id="tab_<?= $lang->id ?>">
                                            <?= $lang->language_code ?>
                                            <?= isset($text->text) ? $text->text : '' ?>

                                            <div class="text-right">
                                                <a class="btn btn-light" href="#" title="Edit" data-toggle="modal"
                                                   data-target="#exampleModalLong<?= $lang->id ?>"><i class="fas fa-edit"></i></a>
                                            </div>
                                            <div class="modal fade" id="exampleModalLong<?= $lang->id ?>" tabindex="-1" role="dialog"
                                                 aria-labelledby="exampleModalLongTitle" style="display: none;" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLongTitle">Edit /start:
                                                                English</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            <p>Text</p>
                                                            <textarea class="form-control" rows="3"><?= isset($text->text) ? $text->text : '' ?></textarea>
                                                        </div>
                                                        <div class="card-footer text-left">
                                                            <button type="submit" class="btn btn-success">Save</button>
                                                            <a class="btn btn-secondary" href="#" data-dismiss="modal" title="Cancel">Cancel</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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



<div class="support-group-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
</div>
