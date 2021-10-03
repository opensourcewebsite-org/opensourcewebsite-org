<?php

use app\models\Contact;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model Contact */
/* @var $form yii\widgets\ActiveForm */

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'userIdOrName')
                                ->textInput([
                                    'data-old-value' => $model->getLinkUserId(),
                                    'value' => $model->getLinkUserId(),
                                ])
                                ->label($model->getAttributeLabel('userIdOrName') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')
                                ->textInput()
                                ->label($model->getAttributeLabel('name') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'is_real')->checkbox(); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'relation')->dropDownList(Contact::RELATION_LABELS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'vote_delegation_priority')
                                ->textInput([
                                    'type' => 'number',
                                    'placeholder' => Yii::t('app', 'Deny'),
                                    'value' => ($model->vote_delegation_priority ?: ''),
                                ])
                                ->label($model->getAttributeLabel('vote_delegation_priority') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'debt_redistribution_priority')
                                ->textInput([
                                    'type' => 'number',
                                    'placeholder' => Yii::t('app', 'Deny'),
                                    'value' => ($model->debt_redistribution_priority ?: ''),
                                ])
                                ->label($model->getAttributeLabel('debt_redistribution_priority') . $labelOptional); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/contact/index') : Url::to(['/contact/view', 'id' => $model->id])?>
                    <?= CancelButton::widget([
                        'url' => $cancelUrl,
                    ]); ?>
                    <?= DeleteButton::widget([
                        'url' => [
                            '/contact/delete-contact',
                            'id' => $model->id,
                        ],
                        'visible' => !$model->isNewRecord && ((string)$model->user_id === (string)Yii::$app->user->id),
                    ]);?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php

$urlRedirect = Yii::$app->urlManager->createUrl(['/contact']);
$jsMessages = [
    'delete-confirm' => Yii::t('app', 'Are you sure you want to delete this contact') . '?',
    'delete-error'   => Yii::t('app', 'Sorry, there was an error while trying to delete the contact') . '.',
    'save-warn-debt' => Yii::t('app', "WARNING!\\n You have changed User.\\n All Debt Redistribution settings related to User \\\"{user}\\\" will be deleted!"),
];

$this->registerJs(
    <<<JS
$("#delete-contact").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("{$jsMessages['delete-confirm']}")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "$urlRedirect";
            } else {
                alert("{$jsMessages['delete-error']}");
            }
        });
    }

    return false;
});

$('#$form->id').on('beforeSubmit', warnOnDeleteDebtRedistributionSettings);

function warnOnDeleteDebtRedistributionSettings() {
    let inputUser = $('#contact-useridorname');
    let newUser = inputUser.val() + '';
    let oldUser = inputUser.attr('data-old-value') + '';
    if (!oldUser || oldUser === 'undefined' || oldUser === newUser) {
        return true;
    }

    return confirm("{$jsMessages['save-warn-debt']}".replace('{user}', oldUser));
}
JS
);
