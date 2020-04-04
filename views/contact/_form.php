<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */
/* @var $form yii\widgets\ActiveForm */

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
                                ->label('User ID / Username (optional)'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')->textInput()->label('Name (optional)'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'debt_redistribution_priority')->textInput(['type' => 'number']); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/contact'], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel'),
                    ]); ?>
                    <?php if (!$model->isNewRecord && $model->user_id === Yii::$app->user->id) : ?>
                        <?= Html::a(Yii::t('app', 'Delete'), ['contact/delete/', 'id' => $model->id], [
                            'class' => 'btn btn-danger float-right',
                            'id' => 'delete-contact'
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
$aMsg = [
    'delete-confirm' => Yii::t('app', 'Are you sure you want to delete this contact?'),
    'delete-error'   => Yii::t('app', 'Sorry, there was an error while trying to delete the contact.'),
    'save-warn-debt' => Yii::t('app', "WARNING!\\n You have changed User.\\n All Debt Redistribution settings related to User \\\"{user}\\\" will be deleted!"),
];

$this->registerJs(<<<JS
$("#delete-contact").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("{$aMsg['delete-confirm']}")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "$urlRedirect";
            } else {
                alert("{$aMsg['delete-error']}");
            }
        });
    }
    
    return false;
});

$('#$form->id').on('beforeSubmit', warnOnDeleteDebtRedistributionSettings);

function warnOnDeleteDebtRedistributionSettings() {
    let newUser = $('#contact-useridorname').val() + '';
    let oldUser = inputUser.attr('data-old-value') + '';
    if (!oldUser || oldUser === newUser) {
        return true;
    }
    
	return confirm("{$aMsg['save-warn-debt']}".replace('{user}', oldUser));
}
JS
);
