<?php
/* @var $this \yii\web\View */

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Edit design');
?>
<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Add New Moqup
        </h3>
    </div>
    <form method="post">
        <input id="form-token" type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>"/>
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="icon fa fa-info"></i> Important!</h5>
                Use UI elements from <?= Html::a('AdminLTE 3', 'https://adminlte.io/themes/dev/AdminLTE/index3.html') ?>, <?= Html::a('Bootstrap 4', 'https://getbootstrap.com/docs/4.1/getting-started/introduction/') ?> and <?= Html::a('Font Awesome 5', 'https://fontawesome.com/icons') ?> examples.
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="pageTitle">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required="required">
                    </div>
                </div>
                <div class="col-md-12">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <a class="nav-link active" href="#html" data-toggle="tab">HTML</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#css" data-toggle="tab">CSS (optional)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#preview" data-toggle="tab">Preview</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="tab-content p-0">
                        <!--                    <div class="tab-pane active show" id="html">
                                                <textarea class="form-control" name="html" rows="10" required="required" onKeyDown="limitText(this.form.html, this.form.countdown, 100000);" onKeyUp="limitText(this.form.html, this.form.countdown, 100000);" maxlength="100000"></textarea>
                                                <font size="1">(Maximum characters: 100000)<br>
                                                You have <input readonly type="text" name="countdown" size="6" value="100000"> characters left.</font>
                                            </div>-->
                        <div class="tab-pane active" id="html">
                            <textarea class="form-control" name="html" rows="10" required="required" maxlength="100000"></textarea>
                        </div>
                        <div class="tab-pane" id="css">
                            <textarea name="css" class="form-control" rows="10" maxlength="100000"></textarea>
                        </div>
                        <div class="tab-pane" id="preview">
                            <p>Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <?= SaveButton::widget(); ?>
            <?= CancelButton::widget([
                'url' => ['moqup/design-list']
            ]); ?>
            <?= DeleteButton::widget([
                'url' => ''
            ]); ?>
        </div>
    </form>
</div>
