<?php

use app\models\Contact;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model Contact */
/* @var $form yii\widgets\ActiveForm */

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
?>

<div class="contact-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'userIdOrName')
                                ->textInput(['data-old-value' => $model->getUserIdOrName()])
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
                            <?= $form->field($model, 'relation')->dropDownList(Contact::RELATIONS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'vote_delegation_priority')
                                ->textInput(['type' => 'number', 'placeholder' => Yii::t('app', 'No priority')])
                                ->label($model->getAttributeLabel('vote_delegation_priority') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'debt_redistribution_priority')
                                ->textInput([
                                    'type' => 'number',
                                    'placeholder' => $model->getAttributeLabel('debt_redistribution_priority:empty'),
                                ])
                                ->label($model->getAttributeLabel('debt_redistribution_priority') . $labelOptional); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget(['url' => '/contact']); ?>
                    <?php if (!$model->isNewRecord && (string)$model->user_id === (string)Yii::$app->user->id) : ?>
                        <?= DeleteButton::widget([
                            'url' => ['contact/delete/', 'id' => $model->id],
                            'options' => [
                                'id' => 'delete-contact'
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php

$urlRedirect = Yii::$app->urlManager->createUrl(['/contact']);
$jsMessages = [
    'delete-confirm' => Yii::t('app', 'Are you sure you want to delete this contact') . '?',
    'delete-error'   => Yii::t('app', 'Sorry, there was an error while trying to delete the contact') . '.',
    'save-warn-debt' => Yii::t('app', "WARNING!\\n You have changed User.\\n All Debt Redistribution settings related to User \\\"{user}\\\" will be deleted!"),
];

$this->registerJs(<<<JS
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
