<?php
use yii\helpers\Html;
use app\models\UserStatistic;

/**
 * @var $this \yii\base\View
 * @var $confirmedUsersCount int
 * @var $dataProvider \yii\data\ArrayDataProvider
 */
?>
<div class="info-box">
    <span class="info-box-icon bg-info"><i class="fa fa-users"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Registered Users</span>
        <span class="info-box-number"><?php echo $confirmedUsersCount; ?></span>
    </div>
</div>

<div class="user-statistics">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Age'), ['user/display', 'type' => UserStatistic::AGE], [
                                'class' => 'nav-link show active'
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= $this->render('parts/_age', ['dataProvider' => $dataProvider]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
