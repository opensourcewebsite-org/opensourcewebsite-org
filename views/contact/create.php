<?php

use yii\helpers\Html;
use app\models\Contact;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'Create Contact');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index', 'view' => Contact::VIEW_USER]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="contact-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
