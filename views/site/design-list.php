<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('menu', 'Moqups');
?>
                            <div class="card-header d-flex p-0">
                                <h3 class="card-title p-3">
                                    Pages
                                </h3>
                                <ul class="nav nav-pills ml-auto p-2">
                                    <li class="nav-item align-self-center mr-4">
                                        <a href="form.html"><button type="button" class="btn btn-outline-success" data-toggle="tooltip" data-placement="top" title="Create New"><i class="fa fa-plus"></i></button></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active show" href="#all" data-toggle="tab">All <span class="badge badge-light ml-1">4</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#your" data-toggle="tab">Your <span class="badge badge-light ml-1">2</span></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-0">
                                <div class="tab-content p-0">
                                    <div class="tab-pane active show" id="all">
                                        <div class="card-body table-responsive p-0">
                                            <table class="table table-hover">
                                                <tbody>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>User</th>
                                                        <th>Date</th>
                                                        <th></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>John Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a> <button type="button" class="btn btn-sm btn-outline-secondary"  data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm btn-outline-danger"  data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>John Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a> <button type="button" class="btn btn-sm btn-outline-secondary"  data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm btn-outline-danger"  data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>Bob Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>Mike Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="following">
                                        <div class="card-body table-responsive p-0">
                                            <table class="table table-hover">
                                                <tbody>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>User</th>
                                                        <th>Date</th>
                                                        <th>Follow</th>
                                                        <th></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>Bob Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td><button type="button" class="btn btn-sm btn-outline-primary active">Following</button></td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="your">
                                        <div class="alert alert-info alert-dismissible m-3">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fa fa-info"></i> Important!</h5>
                                            Your rating is <span class="badge badge-warning">1</span>. You have: <b>3/20 pages</b>, <b>142kb/1Mb space</b>. Contribute to raise your rating! <a href="#">Learn more</a> about rating system.
                                        </div>
                                        <div class="card-body table-responsive p-0">
                                            <table class="table table-hover">
                                                <tbody>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>User</th>
                                                        <th>Date</th>
                                                        <th>Follow</th>
                                                        <th></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>John Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td><span class="text-muted">Your</span></td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a> <button type="button" class="btn btn-sm btn-outline-secondary"  data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm btn-outline-danger"  data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Long page title of the feature proposal</td>
                                                        <td>John Doe</td>
                                                        <td>11-7-2014</td>
                                                        <td><span class="text-muted">Your</span></td>
                                                        <td class="text-right"><a href="" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a> <button type="button" class="btn btn-sm btn-outline-secondary"  data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm btn-outline-danger"  data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
