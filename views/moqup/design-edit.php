<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('menu', 'Edit design');
?>
<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Edit Moqup
        </h3>
    </div>
    <?php
    if (!empty($moqup)) {
        ?>
        <form method="post">
            <input id="form-token" type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>"/>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fa fa-info"></i> Important!</h5>
                    Use UI elements from <?= Html::a('AdminLTE 3', 'https://adminlte.io/themes/dev/AdminLTE/index3.html') ?> and <?= Html::a('Bootstrap 4', 'https://getbootstrap.com/docs/4.1/getting-started/introduction/') ?> examples.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="pageTitle">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required="required" value="<?= $moqup->title; ?>">
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
                            <div class="tab-pane active" id="html">
                                <textarea class="form-control" name="html" rows="10" required="required" maxlength="100000"><?= $moqup->html; ?></textarea>
                            </div>
                            <div class="tab-pane" id="css">
                                <textarea name="css" class="form-control" rows="10" maxlength="100000">
                                    <?php
                                    if (!empty($css)) {
                                        echo $css->css;
                                    }
                                    ?>
                                </textarea>
                            </div>
                            <div class="tab-pane" id="preview">
                                <p>Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Save</button>
                <a type="button" href="<?= Yii::$app->urlManager->createUrl(['moqup/design-list']) ?>" class="btn btn-secondary"> Cancel</a>
                <button type="button" class="btn btn-outline-danger float-right"> Delete</button>
            </div>
        </form>
        <?php
    }
    ?>   
</div>