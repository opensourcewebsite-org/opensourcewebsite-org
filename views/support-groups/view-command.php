<?php

use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\helpers\ArrayHelper;

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
        <?= $this->render('commands/_edit_command', ['model' => $model]) ?>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-condensed">
                    <tbody>
                    <tr>
                        <td>
                            <?= Nav::widget([
                                'options' => [
                                    'class' => 'nav ml-auto p-2 flex-column'
                                ],
                                'items'   => ArrayHelper::merge([
                                    '<li><h4>Languages</h4></li>',
                                ], $model->getNavItems()),
                            ]); ?>
                        </td>
                        <td>

                            <div class="card-body">
                                <div class="tab-content">
                                    <?php foreach ($model->languages as $i => $lang) { ?>
                                        <div class="tab-pane <?= $i == 0 ? 'active show' : '' ?>"
                                             id="tab_<?= $lang->id ?>">

                                            <?php
                                            $textModel = null;

                                            if (\yii\helpers\ArrayHelper::keyExists($lang->language_code, $model->reIndexTexts)) {
                                                $textModel = $model->reIndexTexts[$lang->language_code];
                                            }
                                            ?>
                                            <?= ($textModel) ? nl2br($textModel->text) : '' ?>

                                            <?= $this->render('commands/_edit_text', [
                                                    'lang'      => $lang,
                                                    'model'     => $model,
                                                    'textModel' => $textModel,
                                                ]) ?>
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

