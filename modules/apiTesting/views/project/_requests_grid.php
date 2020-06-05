<?php
/**
 * @var $label \app\modules\apiTesting\models\ApiTestLabel
 */
use app\modules\apiTesting\models\ApiTestRequest;
use yii\grid\GridView;
use yii\helpers\Html;

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    [
                        'format' => 'html',
                        'attribute' => 'name',
                        'value' => function (ApiTestRequest $request) {
                            return Html::a($request->name, ['/apiTesting/request/view', 'id' => $request->id]);
                        }
                    ],
                    'latestResponse.created_at:relativeTime',
                    [
                        'format' => 'html',
                        'value' => function (ApiTestRequest $request) {
                            if ($request->latestResponse) {
                                return $this->render('../common/_test_result_column', [
                                    'response' => $request->latestResponse
                                ]);
                            }
                        }
                    ]
                ],
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
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
