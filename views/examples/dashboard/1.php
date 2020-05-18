<?php

use app\assets\AdminLteContributingAsset;

$this->registerAssetBundle(AdminLteContributingAsset::class);

$this->title = Yii::t('app', 'Dashboard {number}', ['number' => 1]);
$this->params['breadcrumbs'][] = $this->title;

$JS = <<<JS
$(function () {

    'use strict';

    // Make the dashboard widgets sortable Using jquery UI
    $('.connectedSortable').sortable({
        placeholder : 'sort-highlight',
        connectWith : '.connectedSortable',
        handle : '.card-header, .nav-tabs',
        forcePlaceholderSize: true,
        zIndex : 999999
    });

    $('.connectedSortable .card-header, .connectedSortable .nav-tabs-custom').css('cursor', 'move')
});
JS;
$this->registerJs($JS);
?>
<!DOCTYPE html>
<html>

<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-7 connectedSortable">
                <!-- Custom tabs (Charts with tabs)-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Sales
                        </h3>
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                                </li>
                            </ul>
                        </div>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <!-- Morris chart - Sales -->
                            <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;">
                                <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                            </div>
                            <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                                <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div><!-- /.card-body -->
                </div>
                <script>
                    $(function () {

                        'use strict';

                        // Sales chart
                        var salesChartCanvas = document.getElementById('revenue-chart-canvas').getContext('2d');
                        //$('#revenue-chart').get(0).getContext('2d');

                        var salesChartData = {
                            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                            datasets: [
                                {
                                    label: 'Digital Goods',
                                    backgroundColor: 'rgba(60,141,188,0.9)',
                                    borderColor: 'rgba(60,141,188,0.8)',
                                    pointRadius: false,
                                    pointColor: '#3b8bba',
                                    pointStrokeColor: 'rgba(60,141,188,1)',
                                    pointHighlightFill: '#fff',
                                    pointHighlightStroke: 'rgba(60,141,188,1)',
                                    data: [28, 48, 40, 19, 86, 27, 90]
                                },
                                {
                                    label: 'Electronics',
                                    backgroundColor: 'rgba(210, 214, 222, 1)',
                                    borderColor: 'rgba(210, 214, 222, 1)',
                                    pointRadius: false,
                                    pointColor: 'rgba(210, 214, 222, 1)',
                                    pointStrokeColor: '#c1c7d1',
                                    pointHighlightFill: '#fff',
                                    pointHighlightStroke: 'rgba(220,220,220,1)',
                                    data: [65, 59, 80, 81, 56, 55, 40]
                                },
                            ]
                        };

                        var salesChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: {
                                        display: false,
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        display: false,
                                    }
                                }]
                            }
                        };

                        // This will get the first returned node in the jQuery collection.
                        var salesChart = new Chart(salesChartCanvas, {
                                type: 'line',
                                data: salesChartData,
                                options: salesChartOptions
                            }
                        );

                        // Donut Chart
                        var pieChartCanvas = $('#sales-chart-canvas').get(0).getContext('2d');
                        var pieData = {
                            labels: [
                                'Instore Sales',
                                'Download Sales',
                                'Mail-Order Sales',
                            ],
                            datasets: [
                                {
                                    data: [30, 12, 20],
                                    backgroundColor: ['#f56954', '#00a65a', '#f39c12'],
                                }
                            ]
                        };
                        var pieOptions = {
                            legend: {
                                display: false
                            },
                            maintainAspectRatio: false,
                            responsive: true,
                        };
                        //Create pie or douhnut chart
                        // You can switch between pie and douhnut using the method below.
                        var pieChart = new Chart(pieChartCanvas, {
                            type: 'doughnut',
                            data: pieData,
                            options: pieOptions
                        });

                    })
                </script>
                <!-- /.card -->

                <!-- TO DO List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ion ion-clipboard mr-1"></i>
                            To Do List
                        </h3>

                        <div class="card-tools">
                            <ul class="pagination pagination-sm">
                                <li class="page-item"><a href="#" class="page-link">&laquo;</a></li>
                                <li class="page-item"><a href="#" class="page-link">1</a></li>
                                <li class="page-item"><a href="#" class="page-link">2</a></li>
                                <li class="page-item"><a href="#" class="page-link">3</a></li>
                                <li class="page-item"><a href="#" class="page-link">&raquo;</a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <ul class="todo-list" data-widget="todo-list">
                            <li>
                                <!-- drag handle -->
                                <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <!-- checkbox -->
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo1" id="todoCheck1">
                                    <label for="todoCheck1"></label>
                                </div>
                                <!-- todo text -->
                                <span class="text">Design a nice theme</span>
                                <!-- Emphasis label -->
                                <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small>
                                <!-- General tools such as edit or delete-->
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                            <li>
                    <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo2" id="todoCheck2" checked>
                                    <label for="todoCheck2"></label>
                                </div>
                                <span class="text">Make the theme responsive</span>
                                <small class="badge badge-info"><i class="far fa-clock"></i> 4 hours</small>
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                            <li>
                    <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo3" id="todoCheck3">
                                    <label for="todoCheck3"></label>
                                </div>
                                <span class="text">Let theme shine like a star</span>
                                <small class="badge badge-warning"><i class="far fa-clock"></i> 1 day</small>
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                            <li>
                    <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo4" id="todoCheck4">
                                    <label for="todoCheck4"></label>
                                </div>
                                <span class="text">Let theme shine like a star</span>
                                <small class="badge badge-success"><i class="far fa-clock"></i> 3 days</small>
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                            <li>
                    <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo5" id="todoCheck5">
                                    <label for="todoCheck5"></label>
                                </div>
                                <span class="text">Check your messages and notifications</span>
                                <small class="badge badge-primary"><i class="far fa-clock"></i> 1 week</small>
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                            <li>
                    <span class="handle">
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo6" id="todoCheck6">
                                    <label for="todoCheck6"></label>
                                </div>
                                <span class="text">Let theme shine like a star</span>
                                <small class="badge badge-secondary"><i class="far fa-clock"></i> 1 month</small>
                                <div class="tools">
                                    <i class="fas fa-edit"></i>
                                    <i class="fas fa-trash-o"></i>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer clearfix">
                        <button type="button" class="btn btn-info float-right"><i class="fas fa-plus"></i> Add item
                        </button>
                    </div>
                </div>
                <script>
                    $(function () {

                        'use strict';

                        // jQuery UI sortable for the todo list
                        $('.todo-list').sortable({
                            placeholder: 'sort-highlight',
                            handle: '.handle',
                            forcePlaceholderSize: true,
                            zIndex: 999999
                        })
                    })
                </script>
                <!-- /.card -->

                <!-- Calendar -->
                <div class="card bg-gradient-success">
                    <div class="card-header border-0">

                        <h3 class="card-title">
                            <i class="far fa-calendar-alt"></i>
                            Calendar
                        </h3>
                        <!-- tools card -->
                        <div class="card-tools">
                            <!-- button with a dropdown -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-bars"></i></button>
                                <div class="dropdown-menu float-right" role="menu">
                                    <a href="#" class="dropdown-item">Add new event</a>
                                    <a href="#" class="dropdown-item">Clear events</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item">View calendar</a>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- /. tools -->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body pt-0">
                        <!--The calendar -->
                        <div id="calendar" style="width: 100%"></div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <script>
                    $(function () {

                        'use strict';

                        // The Calender
                        $('#calendar').datetimepicker({
                            format: 'L',
                            inline: true
                        })
                    })
                </script>
                <!-- /.card -->
            </section>
            <!-- /.Left col -->
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
            <section class="col-lg-5 connectedSortable">

                <!-- Map card -->
                <div class="card bg-gradient-primary">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Visitors
                        </h3>
                        <!-- card tools -->
                        <div class="card-tools">
                            <button type="button"
                                    class="btn btn-primary btn-sm daterange"
                                    data-toggle="tooltip"
                                    title="Date range">
                                <i class="far fa-calendar-alt"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    data-card-widget="collapse"
                                    data-toggle="tooltip"
                                    title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <!-- /.card-tools -->
                    </div>
                    <div class="card-body">
                        <div id="world-map" style="height: 250px; width: 100%;"></div>
                    </div>
                    <!-- /.card-body-->
                    <div class="card-footer bg-transparent">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div id="sparkline-1"></div>
                                <div class="text-white">Visitors</div>
                            </div>
                            <!-- ./col -->
                            <div class="col-4 text-center">
                                <div id="sparkline-2"></div>
                                <div class="text-white">Online</div>
                            </div>
                            <!-- ./col -->
                            <div class="col-4 text-center">
                                <div id="sparkline-3"></div>
                                <div class="text-white">Sales</div>
                            </div>
                            <!-- ./col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
                <script>
                    $(function () {

                        'use strict';

                        $('.daterange').daterangepicker({
                            ranges: {
                                'Today': [moment(), moment()],
                                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                'This Month': [moment().startOf('month'), moment().endOf('month')],
                                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                            },
                            startDate: moment().subtract(29, 'days'),
                            endDate: moment()
                        }, function (start, end) {
                            window.alert('You chose: ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
                        });

                        /* jQueryKnob */
                        $('.knob').knob();

                        // jvectormap data
                        var visitorsData = {
                            'US': 398, //USA
                            'SA': 400, //Saudi Arabia
                            'CA': 1000, //Canada
                            'DE': 500, //Germany
                            'FR': 760, //France
                            'CN': 300, //China
                            'AU': 700, //Australia
                            'BR': 600, //Brazil
                            'IN': 800, //India
                            'GB': 320, //Great Britain
                            'RU': 3000 //Russia
                        };
                        // World map by jvectormap
                        $('#world-map').vectorMap({
                            map: 'usa_en',
                            backgroundColor: 'transparent',
                            regionStyle: {
                                initial: {
                                    fill: 'rgba(255, 255, 255, 0.7)',
                                    'fill-opacity': 1,
                                    stroke: 'rgba(0,0,0,.2)',
                                    'stroke-width': 1,
                                    'stroke-opacity': 1
                                }
                            },
                            series: {
                                regions: [{
                                    values: visitorsData,
                                    scale: ['#ffffff', '#0154ad'],
                                    normalizeFunction: 'polynomial'
                                }]
                            },
                            onRegionLabelShow: function (e, el, code) {
                                if (typeof visitorsData[code] != 'undefined')
                                    el.html(el.html() + ': ' + visitorsData[code] + ' new visitors')
                            }
                        });
                        // Sparkline charts
                        var sparkline1 = new Sparkline($("#sparkline-1")[0], {
                            width: 80,
                            height: 50,
                            lineColor: '#92c1dc',
                            endColor: '#ebf4f9'
                        });
                        var sparkline2 = new Sparkline($("#sparkline-2")[0], {
                            width: 80,
                            height: 50,
                            lineColor: '#92c1dc',
                            endColor: '#ebf4f9'
                        });
                        var sparkline3 = new Sparkline($("#sparkline-3")[0], {
                            width: 80,
                            height: 50,
                            lineColor: '#92c1dc',
                            endColor: '#ebf4f9'
                        });

                        sparkline1.draw([1000, 1200, 920, 927, 931, 1027, 819, 930, 1021]);
                        sparkline2.draw([515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921]);
                        sparkline3.draw([15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21]);
                    })
                </script>
                <!-- /.card -->

                <!-- solid sales graph -->
                <div class="card bg-gradient-info">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-th mr-1"></i>
                            Sales Graph
                        </h3>

                        <div class="card-tools">
                            <button type="button" class="btn bg-info btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn bg-info btn-sm" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas class="chart" id="line-chart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer bg-transparent">
                        <div class="row">
                            <div class="col-4 text-center">
                                <input type="text" class="knob" data-readonly="true" value="20" data-width="60" data-height="60" data-fgColor="#39CCCC">

                                <div class="text-white">Mail-Orders</div>
                            </div>
                            <!-- ./col -->
                            <div class="col-4 text-center">
                                <input type="text" class="knob" data-readonly="true" value="50" data-width="60" data-height="60" data-fgColor="#39CCCC">

                                <div class="text-white">Online</div>
                            </div>
                            <!-- ./col -->
                            <div class="col-4 text-center">
                                <input type="text" class="knob" data-readonly="true" value="30" data-width="60" data-height="60" data-fgColor="#39CCCC">

                                <div class="text-white">In-Store</div>
                            </div>
                            <!-- ./col -->
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.card-footer -->
                </div>
                <script>
                    $(function () {

                        'use strict';


                        // Sales graph chart
                        var salesGraphChartCanvas = $('#line-chart').get(0).getContext('2d');
                        //$('#revenue-chart').get(0).getContext('2d');

                        var salesGraphChartData = {
                            labels: ['2011 Q1', '2011 Q2', '2011 Q3', '2011 Q4', '2012 Q1', '2012 Q2', '2012 Q3', '2012 Q4', '2013 Q1', '2013 Q2'],
                            datasets: [
                                {
                                    label: 'Digital Goods',
                                    fill: false,
                                    borderWidth: 2,
                                    lineTension: 0,
                                    spanGaps: true,
                                    borderColor: '#efefef',
                                    pointRadius: 3,
                                    pointHoverRadius: 7,
                                    pointColor: '#efefef',
                                    pointBackgroundColor: '#efefef',
                                    data: [2666, 2778, 4912, 3767, 6810, 5670, 4820, 15073, 10687, 8432]
                                }
                            ]
                        };

                        var salesGraphChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false,
                            },
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        fontColor: '#efefef',
                                    },
                                    gridLines: {
                                        display: false,
                                        color: '#efefef',
                                        drawBorder: false,
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        stepSize: 5000,
                                        fontColor: '#efefef',
                                    },
                                    gridLines: {
                                        display: true,
                                        color: '#efefef',
                                        drawBorder: false,
                                    }
                                }]
                            }
                        };

                        // This will get the first returned node in the jQuery collection.
                        var salesGraphChart = new Chart(salesGraphChartCanvas, {
                                type: 'line',
                                data: salesGraphChartData,
                                options: salesGraphChartOptions
                            }
                        )
                    })
                </script>
                <!-- /.card -->

            </section>
            <!-- right col -->
        </div>
        <!-- /.row (main row) -->
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
