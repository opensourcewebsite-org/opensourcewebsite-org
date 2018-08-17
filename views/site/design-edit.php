<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('menu', 'Edit design');
?>
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            New/Edit Page
        </h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="icon fa fa-info"></i> Important!</h5>
            Use UI elements from <?= Html::a('AdminLTE 3', 'https://adminlte.io/themes/dev/AdminLTE/index3.html') ?> and <?= Html::a('Bootstrap 4', 'https://getbootstrap.com/docs/4.1/getting-started/introduction/') ?> examples.
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="pageTitle">Title</label>
                    <input type="text" class="form-control" id="pageTitle">
                </div>
            </div>
            <div class="col-md-12">
                <ul class="nav nav-pills ml-auto p-2">
                    <li class="nav-item">
                        <a class="nav-link active show" href="#html" data-toggle="tab">HTML</a>
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
                    <div class="tab-pane active show" id="html">
                        <textarea class="form-control" rows="10"></textarea>
                    </div>
                    <div class="tab-pane" id="css">
                        <textarea class="form-control" rows="10"></textarea>
                    </div>
                    <div class="tab-pane" id="preview">
                        <p>Coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary"> Cancel</button>
        <button type="button" class="btn btn-outline-danger float-right"> Delete</button>
    </div>
