<?php

use app\models\DebtRedistribution;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\ModalAjax;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\DebtRedistributionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $contact \app\models\Contact */

$this->title                   = 'Debt Redistributions';
$this->params['breadcrumbs'][] = $this->title;

$pjaxId      = 'pjax-grid-debt-redistribution';
$modalId     = 'modal-debt-redistribution-form';
$modalFooter = SaveButton::widget();
$modalFooter.= CancelButton::widget();
?>

<?= ModalAjax::widget([
    'id'            => $modalId,
    'selector'      => '.btn-action:not([data-method="post"])', // all buttons in grid view with href attribute
    'pjaxContainer' => "#$pjaxId",
    'autoClose'     => true,
    'footer'        => $modalFooter,
    'events'        => [
        ModalAjax::EVENT_MODAL_SHOW_COMPLETE => new JsExpression('function(event, xhr, textStatus) {
            if (xhr.status >= 400) {
                osw_alertOnAjaxError(xhr, textStatus);
                jQuery(this).modal("toggle");
                return;
            }

            let header = jQuery(this).find(".modal-header");
            header.children(":not(.close)").remove();
            header.append(jQuery("#headerForModal").children());
        }'),
        ModalAjax::EVENT_MODAL_SUBMIT => new JsExpression("function(event, data, status, xhr) {
            if (data.validation) {
                jQuery('#active-form-debt-redistribution').yiiActiveForm('updateMessages', data.validation, true);
            } else if (!data.success) {
                osw_alertOnAjaxError(jqXHR, 'Incorrect server response');
            } else {
                jQuery(this).modal('toggle');
                jQuery.pjax.reload({
                    container: '#$pjaxId',
                    timeout: 1000,
                    replace: false,
                    url: jQuery('#modal-debt-redistribution-table').data('kbModalAjax').initalRequestUrl,
                });
            }
        }"),
        ModalAjax::EVENT_MODAL_SUBMIT_COMPLETE => new JsExpression('function(event, xhr, status) {
            if (xhr.status >= 400) {
                osw_alertOnAjaxError(xhr);
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        }'),
    ],
]);?>

<div class="debt-redistribution-index">

    <?php Pjax::begin([
        'id'                 => $pjaxId,
        'enablePushState'    => false,
        'enableReplaceState' => false,
        'options'            => [
            'data-pjax-replace-state' => 0,
            'data-pjax-push-state'    => 0,
        ],
    ]); ?>

        <p class="text-right">
            <?= Html::a(
                Yii::t('app', 'Create'),
                ['/debt-redistribution/form', 'contactId' => $contact->id],
                [
                    'data-pjax' => '0',
                    'class'     => 'btn-action btn btn-success',
                ]
            ); ?>
        </p>

        <?= GridView::widget([
            'id'           => 'grid-debt-redistribution',
            'dataProvider' => $dataProvider,
            'columns'      => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'currency_id',
                    'value'     => function (DebtRedistribution $model) {
                        return $model->currency->code;
                    },
                ],
                [
                    'attribute' => 'max_amount',
                    'value'     => function (DebtRedistribution $model) {
                        return $model->max_amount ?? Yii::t('app', 'No limit');
                    },
                ],

                [
                    'class'    => 'yii\grid\ActionColumn',
                    'template' => '{form} {delete}',
                    'buttons'  => [
                        'form' => static function ($url) {
                            return Html::a('<i class="fa fa-edit"></i>', $url, [
                                'title'      => Yii::t('yii', 'Update'),
                                'aria-label' => Yii::t('yii', 'Update'),
                                'data-pjax'  => '0',
                                'class'      => 'btn-action',
                            ]);
                        },
                        'delete' => static function ($url) {
                            return Html::a('<i class="fa fa-trash"></i>', $url, [
                                'title'        => Yii::t('yii', 'Delete'),
                                'aria-label'   => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-pjax'    => '1',
                                'data-method'  => 'post',
                                'class'        => 'btn-action',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>

    <?php Pjax::end(); ?>

</div>
