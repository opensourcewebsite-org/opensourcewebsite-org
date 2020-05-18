<?php

use app\assets\AdminLteContributingAsset;

$this->registerAssetBundle(AdminLteContributingAsset::class);

$this->title = Yii::t('app', 'Dashboard {number}', ['number' => 3]);
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Online Store Visitors</h3>
                            <a href="javascript:void(0);">View Report</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span class="text-bold text-lg">820</span>
                                <span>Visitors Over Time</span>
                            </p>
                            <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> 12.5%
                    </span>
                                <span class="text-muted">Since last week</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4">
                            <canvas id="visitors-chart" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> This Week
                  </span>

                            <span>
                    <i class="fas fa-square text-gray"></i> Last Week
                  </span>
                        </div>
                    </div>
                </div>
                <script>
                    $(function () {

                        'use strict';

                        var ticksStyle = {
                            fontColor: '#495057',
                            fontStyle: 'bold'
                        };

                        var mode = 'index';
                        var intersect = true;
                        var $visitorsChart = $('#visitors-chart');
                        var visitorsChart = new Chart($visitorsChart, {
                            data: {
                                labels: ['18th', '20th', '22nd', '24th', '26th', '28th', '30th'],
                                datasets: [{
                                    type: 'line',
                                    data: [100, 120, 170, 167, 180, 177, 160],
                                    backgroundColor: 'transparent',
                                    borderColor: '#007bff',
                                    pointBorderColor: '#007bff',
                                    pointBackgroundColor: '#007bff',
                                    fill: false
                                    // pointHoverBackgroundColor: '#007bff',
                                    // pointHoverBorderColor : '#007bff'
                                },
                                    {
                                        type: 'line',
                                        data: [60, 80, 70, 67, 80, 77, 100],
                                        backgroundColor: 'tansparent',
                                        borderColor: '#ced4da',
                                        pointBorderColor: '#ced4da',
                                        pointBackgroundColor: '#ced4da',
                                        fill: false
                                        // pointHoverBackgroundColor: '#ced4da',
                                        // pointHoverBorderColor : '#ced4da'
                                    }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                tooltips: {
                                    mode: mode,
                                    intersect: intersect
                                },
                                hover: {
                                    mode: mode,
                                    intersect: intersect
                                },
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        // display: false,
                                        gridLines: {
                                            display: true,
                                            lineWidth: '4px',
                                            color: 'rgba(0, 0, 0, .2)',
                                            zeroLineColor: 'transparent'
                                        },
                                        ticks: $.extend({
                                            beginAtZero: true,
                                            suggestedMax: 200
                                        }, ticksStyle)
                                    }],
                                    xAxes: [{
                                        display: true,
                                        gridLines: {
                                            display: false
                                        },
                                        ticks: ticksStyle
                                    }]
                                }
                            }
                        })
                    })
                </script>
                <!-- /.card -->

                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Products</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Sales</th>
                                <th>More</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <img src="https://dummyimage.com/150x150/a31f9c/ffffff&text=avatar" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Some Product
                                </td>
                                <td>$13 USD</td>
                                <td>
                                    <small class="text-success mr-1">
                                        <i class="fas fa-arrow-up"></i>
                                        12%
                                    </small>
                                    12,000 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://dummyimage.com/150x150/a31f9c/ffffff&text=avatar" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Another Product
                                </td>
                                <td>$29 USD</td>
                                <td>
                                    <small class="text-warning mr-1">
                                        <i class="fas fa-arrow-down"></i>
                                        0.5%
                                    </small>
                                    123,234 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://dummyimage.com/150x150/a31f9c/ffffff&text=avatar" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Amazing Product
                                </td>
                                <td>$1,230 USD</td>
                                <td>
                                    <small class="text-danger mr-1">
                                        <i class="fas fa-arrow-down"></i>
                                        3%
                                    </small>
                                    198 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://dummyimage.com/150x150/a31f9c/ffffff&text=avatar" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Perfect Item
                                    <span class="badge bg-danger">NEW</span>
                                </td>
                                <td>$199 USD</td>
                                <td>
                                    <small class="text-success mr-1">
                                        <i class="fas fa-arrow-up"></i>
                                        63%
                                    </small>
                                    87 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Sales</h3>
                            <a href="javascript:void(0);">View Report</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span class="text-bold text-lg">$18,230.00</span>
                                <span>Sales Over Time</span>
                            </p>
                            <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> 33.1%
                    </span>
                                <span class="text-muted">Since last month</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4">
                            <canvas id="sales-chart" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> This year
                  </span>

                            <span>
                    <i class="fas fa-square text-gray"></i> Last year
                  </span>
                        </div>
                    </div>
                </div>
                <script>
                    $(function () {

                        'use strict';

                        var ticksStyle = {
                            fontColor: '#495057',
                            fontStyle: 'bold'
                        };

                        var mode = 'index';
                        var intersect = true;

                        var $salesChart = $('#sales-chart');
                        var salesChart = new Chart($salesChart, {
                            type: 'bar',
                            data: {
                                labels: ['JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                                datasets: [
                                    {
                                        backgroundColor: '#007bff',
                                        borderColor: '#007bff',
                                        data: [1000, 2000, 3000, 2500, 2700, 2500, 3000]
                                    },
                                    {
                                        backgroundColor: '#ced4da',
                                        borderColor: '#ced4da',
                                        data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
                                    }
                                ]
                            },
                            options: {
                                maintainAspectRatio: false,
                                tooltips: {
                                    mode: mode,
                                    intersect: intersect
                                },
                                hover: {
                                    mode: mode,
                                    intersect: intersect
                                },
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        // display: false,
                                        gridLines: {
                                            display: true,
                                            lineWidth: '4px',
                                            color: 'rgba(0, 0, 0, .2)',
                                            zeroLineColor: 'transparent'
                                        },
                                        ticks: $.extend({
                                            beginAtZero: true,

                                            // Include a dollar sign in the ticks
                                            callback: function (value, index, values) {
                                                if (value >= 1000) {
                                                    value /= 1000;
                                                    value += 'k'
                                                }
                                                return '$' + value
                                            }
                                        }, ticksStyle)
                                    }],
                                    xAxes: [{
                                        display: true,
                                        gridLines: {
                                            display: false
                                        },
                                        ticks: ticksStyle
                                    }]
                                }
                            }
                        })
                    })
                </script>
                <!-- /.card -->

                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Online Store Overview</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-sm btn-tool">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-tool">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                            <p class="text-success text-xl">
                                <i class="ion ion-ios-refresh-empty"></i>
                            </p>
                            <p class="d-flex flex-column text-right">
                    <span class="font-weight-bold">
                      <i class="ion ion-android-arrow-up text-success"></i> 12%
                    </span>
                                <span class="text-muted">CONVERSION RATE</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->
                        <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                            <p class="text-warning text-xl">
                                <i class="ion ion-ios-cart-outline"></i>
                            </p>
                            <p class="d-flex flex-column text-right">
                    <span class="font-weight-bold">
                      <i class="ion ion-android-arrow-up text-warning"></i> 0.8%
                    </span>
                                <span class="text-muted">SALES RATE</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <p class="text-danger text-xl">
                                <i class="ion ion-ios-people-outline"></i>
                            </p>
                            <p class="d-flex flex-column text-right">
                    <span class="font-weight-bold">
                      <i class="ion ion-android-arrow-down text-danger"></i> 1%
                    </span>
                                <span class="text-muted">REGISTRATION RATE</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->
                    </div>
                </div>
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>

<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</a>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->

</html>

