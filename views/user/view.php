<?php

use app\components\Converter;
use app\models\Rating;
use app\models\User;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
$this->title = 'Profile View';
$publicLink = Url::toRoute([
    '/user/view/'.$model->id,
], true);
?>

<div class="user-profile-form">
   
    
    <div class="row">
        <div class="col-md-12">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'ID',
                        'value' => function ($model) {
                            return $model->id ?? $model->id;
                        }
                    ],
                    [
                        'label' => 'Name',
                        'value' => function ($model) {
                            return $model->name ?? $model->id;
                        }
                    ],
                    [
                        'label' => 'Username',
                        'value' => function ($model) {
                            return $model->username ?? $model->id;
                        }
                    ],
                    
                ],
            ]); ?>
        </div>
    </div>
</div>

<!--<div class="site-error">
 
    <p>
        Example for public profile url: <?php echo $publicLink;?>.
    </p>

</div>-->
