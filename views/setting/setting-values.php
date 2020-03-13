<?php

use app\components\helpers\SettingHelper;
use app\models;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel models\IssueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $settingValues models\SettingValue[] */
/* @var $setting models\Setting */
/* @var $settingsConfig string */

$this->title = Yii::t('app', 'View website setting').': '.$setting->key;
?>
<?php $this->beginBlock('content-header-data'); ?>
        <div class="row mb-2">
			<div class="col-sm-12">
				<h1 class="m-0 text-dark"><?= Html::encode($this->title) ?></h1>
			</div>
        </div>
<?php $this->endBlock(); ?>
 <section class="content">
      <div class="row">
          <div class="col-md-12">
            <div class="card">
                <div class="card-header text-right">
					<a class="btn btn-success ml-3" href="#" title="New Value" data-toggle="modal" data-target="#exampleModalLong" >New value</a>
					<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLongTitle">Add value</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body text-left">
									<p>Value</p>
									<input type="text" class="form-control" id="newValue" >
									<div class="error"></div>
							</div>
							<div class="card-footer text-left">
									<button type="button" class="btn btn-success saveValue">Save</button>
									<a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
								</div>
							</div>
						</div>
					</div>
				 </div>
                <div class="card-body p-0">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th width="5%"></th>
							<th width="40%">Value</th>
							<th width="25%">Vote</th>
							<th width="30%" class="ml-5" ></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<i class="fas fa-crown text-warning" data-toggle="tooltip" title="Current value"></i>
							</td>
							<td>
								<label class="form-check-label" for="exampleRadios2">
									<?php echo $existingValues[] = $setting->value; ?>
								</label>
							</td>
							<td>
								<?=SettingHelper::getVoteHTMl('', $setting->getCurrentValueVotes(false));?>
							</td>
							<td>

								<?php
									$settingVote = $setting->getSettingValueUserVote();
									$defaultValue = $setting->getDefaultSettingValue();
									if (!empty($settingVote) && $settingVote->settingValue->is_current != 1): ?>

									<input class="btn btn-default ml-5 vote" type="button" name="exampleRadios" id="exampleRadios2" value="Vote" data-id = '<?=!empty($defaultValue->id) ? $defaultValue->id : -1?>'>

                                <?php else: ?>

                                    <span class="badge badge-primary ml-5">Your vote</span>

                                <?php endif;?>

							</td>
						</tr>
                        <?php foreach ($settingValues as $settingValue): ?>
						<tr>
							<td></td>
							<td>
								<label class="form-check-label" for="exampleRadios2">
									<?php echo $existingValues[] = $settingValue->value; ?>
								</label>
							</td>
							<td>
                                <?=SettingHelper::getVoteHTMl($settingValue);?>
							</td>
							<td>
                                <?php if (empty($settingValue->getSettingValueUserVote()->id)): ?>
							    	<input class="btn btn-default ml-5 vote" type="button" name="exampleRadios" id="exampleRadios2" value="Vote" data-id="<?=$settingValue->id?>">
                                <?php else: ?>
                                    <span class="badge badge-primary ml-5">Your vote</span>
                                <?php endif;?>
							</td>
						</tr>
                        <?php endforeach?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
      </div>
</section>

<?php
$url = Yii::$app->urlManager->createUrl(['setting/vote']);
$urlNewValue = Yii::$app->urlManager->createUrl(['setting/create-value']);
$existingValues = json_encode($existingValues);

$script = <<<JS
var setting_id = {$setting->id};
var setting_key = '{$setting->key}';
const settingsConfig = JSON.parse('{$settingsConfig}');
const existingValues = JSON.parse('{$existingValues}');
var decimalRegex= /^-?[0-9]+(\.[0-9]+)?$/;
var integerRegex= /^-?[0-9]+$/;

$(".vote").on("click", function(event) {
    event.preventDefault();
	var value_id = '';

	if($(this).attr('data-id'))
    	value_id = $(this).attr('data-id');

    if (confirm('Are you sure you want to vote for this value?')) {
        $.post('{$url}', {'value_id':value_id, 'setting_id':setting_id}, function(result) {
            if (result == "1") {
                location.reload();
            }
            else {
                alert('Sorry, there was an error while trying to vote the value');
            }
        });
    }

    return false;
});

$(".saveValue").on("click", function(event) {
    event.preventDefault();
    const new_value = $('#newValue').val();
	var valid = false;
	var regEx;
	const rules = settingsConfig[setting_key];
	var errorMsg = 'unique ';

	$('.error').text('');

	if (rules.validation.sign == 'positive') {
		valid = new_value > 0;
		errorMsg += 'positive '+rules.type;
	}

	if(existingValues.indexOf(new_value) != -1) {
		valid = false;
	}

	if (valid && rules.validation.max != undefined)	{
		valid = new_value < rules.validation.max;
		errorMsg += ', max ' + rules.validation.max;
	}

	if (valid) {
		switch(rules.type)
		{
			case 'integer':		regEx = integerRegex;
								break;
			case 'fractional': 	regEx = decimalRegex;
								break;
		}

		if (new_value.match(regEx) == null) {
			valid = false;
		}
		else {
			valid = true;
		}
	}

    if (!valid) {
		$('.error').text('Please enter correct values: '+errorMsg);
	}
	else {
        $.post('{$urlNewValue}', {'new_value':new_value, 'setting_id':setting_id}, function(result) {
            if (result == "1") {
                location.reload();
            }
            else {
                alert('Sorry, there was an error while saving the value');
            }
        });
    }

    return false;
});
JS;
$this->registerJs($script);
