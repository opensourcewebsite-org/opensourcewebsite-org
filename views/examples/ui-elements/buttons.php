<?php

use app\assets\widgets\CommonAsset;

$this->registerAssetBundle(CommonAsset::class);
?>
<!DOCTYPE html>
<html>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Buttons
                        </h3>
                    </div>
                    <div class="card-body pad table-responsive">
                        <p>Various types of buttons. Using the base class <code>.btn</code></p>
                        <table class="table table-bordered text-center">
                            <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-default">Default</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-default btn-lg">Default</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-default btn-sm">Default</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-default btn-xs">Default</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-default btn-flat">Default</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-default disabled">Default</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary btn-lg">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary btn-sm">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary btn-xs">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary btn-flat">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-primary disabled">Primary</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary btn-lg">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary btn-sm">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary btn-xs">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary btn-flat">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-secondary disabled">Secondary
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-success">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-success btn-lg">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-success btn-sm">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-success btn-xs">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-success btn-flat">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-success disabled">Success</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-info">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-info btn-lg">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-info btn-sm">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-info btn-xs">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-info btn-flat">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-info disabled">Info</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger btn-lg">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger btn-sm">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger btn-xs">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger btn-flat">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-danger disabled">Danger</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning btn-lg">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning btn-sm">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning btn-xs">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning btn-flat">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-warning disabled">Warning</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- ./row -->

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Outline Buttons
                        </h3>
                    </div>
                    <div class="card-body pad table-responsive">
                        <p>Various types of buttons. Using the base class <code>.btn</code></p>
                        <table class="table table-bordered text-center">
                            <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary btn-lg">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary btn-sm">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary btn-xs">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary btn-flat">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-primary disabled">Primary
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary btn-lg">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary btn-sm">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary btn-xs">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary btn-flat">
                                        Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-secondary disabled">
                                        Secondary
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success btn-lg">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success btn-sm">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success btn-xs">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success btn-flat">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-success disabled">Success
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info btn-lg">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info btn-sm">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info btn-xs">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info btn-flat">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-info disabled">Info</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger btn-lg">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger btn-sm">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger btn-xs">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger btn-flat">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-danger disabled">Danger
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning btn-lg">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning btn-sm">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning btn-xs">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning btn-flat">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block btn-outline-warning disabled">Warning
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- ./row -->

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit"></i>
                            Gradient Buttons (bg-gradient-*)
                        </h3>
                    </div>
                    <div class="card-body pad table-responsive">
                        <p>Various types of buttons. Using the base class <code>.btn</code></p>
                        <table class="table table-bordered text-center">
                            <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary">Primary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary btn-lg">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary btn-sm">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary btn-xs">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary btn-flat">Primary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-primary disabled">Primary
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary">Secondary</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary btn-xs">Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary btn-flat">
                                        Secondary
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-secondary disabled">
                                        Secondary
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success">Success</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success btn-lg">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success btn-sm">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success btn-xs">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success btn-flat">Success
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-success disabled">Success
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info btn-lg">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info btn-sm">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info btn-xs">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info btn-flat">Info</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-info disabled">Info</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger">Danger</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger btn-lg">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger btn-sm">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger btn-xs">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger btn-flat">Danger
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-danger disabled">Danger
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning">Warning</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning btn-lg">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning btn-sm">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning btn-xs">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning btn-flat">Warning
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-block bg-gradient-warning disabled">Warning
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- ./row -->
        <div class="row">
            <div class="col-md-6">
                <!-- Block buttons -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Block Buttons</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-default btn-block">.btn-block</button>
                        <button type="button" class="btn btn-default btn-block btn-flat">.btn-block .btn-flat</button>
                        <button type="button" class="btn btn-default btn-block btn-sm">.btn-block .btn-sm</button>
                    </div>
                </div>
                <!-- /.card -->

                <!-- Horizontal grouping -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Horizontal Button Group</h3>
                    </div>
                    <div class="card-body table-responsive pad">
                        <p>
                            Horizontal button groups are easy to create with bootstrap. Just add your buttons
                            inside <code>&lt;div class="btn-group"&gt;&lt;/div&gt;</code>
                        </p>

                        <table class="table table-bordered">
                            <tr>
                                <th>Button</th>
                                <th>Icons</th>
                                <th>Flat</th>
                                <th>Dropdown</th>
                            </tr>
                            <!-- Default -->
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default">Left</button>
                                        <button type="button" class="btn btn-default">Middle</button>
                                        <button type="button" class="btn btn-default">Right</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-default">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-default"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default">1</button>
                                        <button type="button" class="btn btn-default">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- ./default -->
                            <!-- Info -->
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info">Left</button>
                                        <button type="button" class="btn btn-info">Middle</button>
                                        <button type="button" class="btn btn-info">Right</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-right"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info">1</button>
                                        <button type="button" class="btn btn-info">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /. info -->
                            <!-- /.danger -->
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-danger">Left</button>
                                        <button type="button" class="btn btn-danger">Middle</button>
                                        <button type="button" class="btn btn-danger">Right</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-right"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-danger">1</button>
                                        <button type="button" class="btn btn-danger">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-danger dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.danger -->
                            <!-- warning -->
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning">Left</button>
                                        <button type="button" class="btn btn-warning">Middle</button>
                                        <button type="button" class="btn btn-warning">Right</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-warning"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning">1</button>
                                        <button type="button" class="btn btn-warning">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.warning -->
                            <!-- success -->
                            <tr>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success">Left</button>
                                        <button type="button" class="btn btn-success">Middle</button>
                                        <button type="button" class="btn btn-success">Right</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-success">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-success"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success">1</button>
                                        <button type="button" class="btn btn-success">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.success -->
                        </table>
                    </div>
                </div>
                <!-- /.card -->

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Appended Buttons</h3>
                    </div>
                    <div class="card-body">
                        <strong>With dropdown</strong>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    Action
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <!-- /btn-group -->
                            <input type="text" class="form-control">
                        </div>
                        <!-- /input-group -->
                        <strong>Normal</strong>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Action</button>
                            </div>
                            <!-- /btn-group -->
                            <input type="text" class="form-control">
                        </div>
                        <!-- /input-group -->
                        <strong>Flat</strong>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control rounded-0">
                            <span class="input-group-append">
                                <button type="button" class="btn btn-info btn-flat">Go!</button>
                            </span>
                        </div>
                        <!-- /input-group -->
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <!-- split buttons box -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Split buttons</h3>
                    </div>
                    <div class="card-body">
                        <!-- Split button -->
                        <p class="mb-1">Normal split buttons:</p>

                        <div class="margin">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default">Action</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info">Action</button>
                                <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger">Action</button>
                                <button type="button" class="btn btn-danger dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success">Action</button>
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning">Action</button>
                                <button type="button" class="btn btn-warning dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- flat split buttons -->
                        <p class="mt-3 mb-1">Flat split buttons:</p>

                        <div class="margin">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-flat">Action</button>
                                <button type="button" class="btn btn-default btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-flat">Action</button>
                                <button type="button" class="btn btn-info btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger btn-flat">Action</button>
                                <button type="button" class="btn btn-danger btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-flat">Action</button>
                                <button type="button" class="btn btn-success btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning btn-flat">Action</button>
                                <button type="button" class="btn btn-warning btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Split button -->
                        <p class="mt-3 mb-1">Hoverable split buttons:</p>
                        <div class="margin">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default">Action</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                            </div>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info">Action</button>
                                <button type="button" class="btn btn-info dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger">Action</button>
                                <button type="button" class="btn btn-danger dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success">Action</button>
                                <button type="button" class="btn btn-success dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning">Action</button>
                                <button type="button" class="btn btn-warning dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </button>
                            </div>
                        </div>


                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- end split buttons box -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
                <!-- Application buttons -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Application Buttons</h3>
                    </div>
                    <div class="card-body">
                        <p>Add the classes <code>.btn.btn-app</code> to an <code>&lt;a></code> tag to achieve the
                            following:</p>
                        <a class="btn btn-app">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a class="btn btn-app">
                            <i class="fas fa-play"></i> Play
                        </a>
                        <a class="btn btn-app">
                            <i class="fas fa-pause"></i> Pause
                        </a>
                        <a class="btn btn-app">
                            <i class="fas fa-save"></i> Save
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-warning">3</span>
                            <i class="fas fa-bullhorn"></i> Notifications
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-success">300</span>
                            <i class="fas fa-barcode"></i> Products
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-purple">891</span>
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-teal">67</span>
                            <i class="fas fa-inbox"></i> Orders
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-info">12</span>
                            <i class="fas fa-envelope"></i> Inbox
                        </a>
                        <a class="btn btn-app">
                            <span class="badge bg-danger">531</span>
                            <i class="fas fa-heart"></i> Likes
                        </a>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- Vertical grouping -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vertical Button Group</h3>
                    </div>
                    <div class="card-body table-responsive pad">

                        <p>
                            Vertical button groups are easy to create with bootstrap. Just add your buttons
                            inside <code>&lt;div class="btn-group-vertical"&gt;&lt;/div&gt;</code>
                        </p>

                        <table class="table table-bordered">
                            <tr>
                                <th>Button</th>
                                <th>Icons</th>
                                <th>Flat</th>
                                <th>Dropdown</th>
                            </tr>
                            <!-- Default -->
                            <tr>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-default">Top</button>
                                        <button type="button" class="btn btn-default">Middle</button>
                                        <button type="button" class="btn btn-default">Bottom</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-default"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-default">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-default"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-default">1</button>
                                        <button type="button" class="btn btn-default">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- ./default -->
                            <!-- Info -->
                            <tr>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-info">Top</button>
                                        <button type="button" class="btn btn-info">Middle</button>
                                        <button type="button" class="btn btn-info">Bottom</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-info"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-info btn-flat">
                                            <i class="fas fa-align-right"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-info">1</button>
                                        <button type="button" class="btn btn-info">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /. info -->
                            <!-- /.danger -->
                            <tr>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-danger">Top</button>
                                        <button type="button" class="btn btn-danger">Middle</button>
                                        <button type="button" class="btn btn-danger">Bottom</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-flat">
                                            <i class="fas fa-align-right"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-danger">1</button>
                                        <button type="button" class="btn btn-danger">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.danger -->
                            <!-- warning -->
                            <tr>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-warning">Top</button>
                                        <button type="button" class="btn btn-warning">Middle</button>
                                        <button type="button" class="btn btn-warning">Bottom</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-warning"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-warning"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-warning">1</button>
                                        <button type="button" class="btn btn-warning">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.warning -->
                            <!-- success -->
                            <tr>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-success">Top</button>
                                        <button type="button" class="btn btn-success">Middle</button>
                                        <button type="button" class="btn btn-success">Bottom</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-success"><i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-success">
                                            <i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-success"><i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-flat">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-success">1</button>
                                        <button type="button" class="btn btn-success">2</button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                                <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- /.success -->
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- Radio Buttons -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Radio Button Group</h3>
                    </div>
                    <div class="card-body table-responsive pad">
                        <p class="mb-1">Radio Button Group with <code>.btn-secondary</code></p>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-secondary active">
                                <input type="radio" name="options" id="option1" autocomplete="off" checked> Active
                            </label>
                            <label class="btn btn-secondary">
                                <input type="radio" name="options" id="option2" autocomplete="off"> Radio
                            </label>
                            <label class="btn btn-secondary">
                                <input type="radio" name="options" id="option3" autocomplete="off"> Radio
                            </label>
                        </div>

                        <p class="mt-3 mb-1">Radio Button Group with <code>.bg-olive</code></p>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn bg-olive active">
                                <input type="radio" name="options" id="option1" autocomplete="off" checked> Active
                            </label>
                            <label class="btn bg-olive">
                                <input type="radio" name="options" id="option2" autocomplete="off"> Radio
                            </label>
                            <label class="btn bg-olive">
                                <input type="radio" name="options" id="option3" autocomplete="off"> Radio
                            </label>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /. row -->
    </div><!-- /.container-fluid -->
</section>

<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</a>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->

</html>
