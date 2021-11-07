<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\components\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use app\models\User;
use yii\helpers\Url;

/**
 * @var View $this
 */

$this->title = ($user->isBasicIncomeParticipant() ? Yii::t('app', 'Participant') : Yii::t('app', 'Candidate')) . ': ' . $user->getDisplayName();
$this->params['breadcrumbs'][] = 'Stellar';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Basic income'), 'url' => ['index']];
if ($user->isBasicIncomeParticipant()) {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Participants'), 'url' => ['participant']];
} else {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Candidates'), 'url' => ['candidate']];
}
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Positive votes'); ?>: <?= $user->getBasicIncomePositiveVotesCount() ?></h3>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'id' => 'users',
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => Yii::t('app', 'Rank'),
                                'content' => function (User $model) {
                                    return $model->getRank();
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'User'),
                                'content' => function (User $model) {
                                    return Html::a($model->getDisplayName(), ['contact/view-user', 'id' => $model->id]);
                                },
                                'format' => 'html',
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
</div>
