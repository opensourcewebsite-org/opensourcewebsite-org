<?php

declare(strict_types=1);

use app\components\helpers\SettingHelper;
use app\components\helpers\Html;
use app\widgets\buttons\AddButton;
use app\widgets\ModalAjax;
use yii\helpers\Url;
use yii\grid\GridView;
use app\widgets\buttons\SelectButton;

/* @var $this yii\web\View */
/* @var $searchModel app\models\IssueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Website setting') . ': ' . $setting->key;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Website settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . ($setting->id ?? ' NEW');
?>
 <section class="content">
      <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <?= ModalAjax::widget([
                            'id' => 'add-value',
                            'header' => Yii::t('app', 'New Value'),
                            'toggleButton' => [
                                'label' => Html::icon('add'),
                                'title' => Yii::t('app', 'New Value'),
                                'class' => 'btn btn-outline-success',
                                'style' => [
                                    'float' => 'right',
                                ],
                            ],
                            'url' => Url::to([
                                'setting/add-value',
                                'setting_key' => $setting->key ?? null,
                            ]),
                        ]);?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => [
                            'class' => 'table table-hover',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'value',
                                'value' => function ($model) {
                                    return ($model->isCurrent() ? '<i class="fas fa-crown text-warning" data-toggle="tooltip" title="' . Yii::t('app', 'Current value') . '"></i> ' : null) . $model->value;
                                },
                                'format' => 'html',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Votes') . ' %',
                                'value' => function ($model) {
                                    return $model->getVotesPercent();
                                },
                                'format' => 'html',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Votes'),
                                'value' => function ($model) {
                                    return SettingHelper::getVotesHTMl($model);
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                                'contentOptions' => [
                                    'style' => 'width:100%;',
                                ],
                            ],
                            [
                                'value' => function ($model) {
                                    $vote = $model->setting->getSettingValueVoteByUserId();

                                    if ($vote && ($model->id == $vote->getSettingValueId())) {
                                        return '<span class="badge badge-primary ml-5">' . Yii::t('app', 'Your Vote') . '</span>';
                                    } else {
                                        return SelectButton::widget([
                                            'text' => Yii::t('app', 'Vote'),
                                            'options' => [
                                                'title' => Yii::t('app', 'Vote'),
                                                'style' => 'float: right;',
                                                'data-params' => [
                                                     'setting_value_id' => $model->id,
                                                ],
                                            ],
                                            'url' => [
                                                '/setting/vote',
                                            ],
                                        ]);
                                    }
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                            ],
                        ],
                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
			</div>
		</div>
      </div>
</section>
