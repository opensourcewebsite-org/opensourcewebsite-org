<?php

use yii\helpers\Html;
use app\models\Contact;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'Update Contact: ' . $model->getContactName(), [
        'nameAttribute' => '' . $model->getContactName(),
    ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index', 'view' => Contact::VIEW_USER]];
$this->params['breadcrumbs'][] = ['label' => $model->getContactName(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="contact-update">

    <?= $this->render('_form', [
        'model' => $model,
        'relations' => Contact::RELATIONS,
    ]); ?>

</div>
