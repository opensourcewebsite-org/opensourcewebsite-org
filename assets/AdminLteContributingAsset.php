<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * AdminLte asset bundle.
 */
class AdminLteContributingAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/css/OverlayScrollbars.min.css',
        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.css',
        'https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.css',
        'https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/css/tempusdominus-bootstrap-4.min.css',
        'https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',

        'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
        'plugins/fontawesome-free/css/all.min.css',

        //ui sliders
        'plugins/ion-rangeslider/css/ion.rangeSlider.min.css',

        //ui modals
        'plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
        'plugins/toastr/toastr.min.css',

        //forms advanced
        'plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css',
        'plugins/select2/css/select2.min.css',
        'plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',
        'plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css',

        //trumbowyg
        'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.20.0/ui/trumbowyg.min.css',

        //tables data tables
        'plugins/datatables-bs4/css/dataTables.bootstrap4.css',

        //tables jsgrid
        'plugins/jsgrid/jsgrid.min.css',
        'plugins/jsgrid/jsgrid-theme.min.css',

        //calendar
        'plugins/fullcalendar/main.min.css',
        'plugins/fullcalendar-interaction/main.min.css',
        'plugins/fullcalendar-daygrid/main.min.css',
        'plugins/fullcalendar-timegrid/main.min.css',
        'plugins/fullcalendar-bootstrap/main.min.css',

        //gallery
        'plugins/ekko-lightbox/ekko-lightbox.css',

    ];

    public $js = [
//        'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',
//        'https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js',
//        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js',
//        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.usa.js',
//        'https://cdn.jsdelivr.net/npm/jquery-knob@1.2.11/dist/jquery.knob.min.js',
//        'https://cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
//        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.js',
//        'https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/js/tempusdominus-bootstrap-4.min.js',
//        'https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.js',
//        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/js/jquery.overlayScrollbars.min.js',
//        'https://cdn.jsdelivr.net/npm/jquery-mousewheel@3.1.13/jquery.mousewheel.js',
//        'https://cdn.jsdelivr.net/npm/raphael@2.3.0/raphael.min.js',
//        'https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/js/jquery.mapael.min.js',
//        'https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/js/maps/usa_states.min.js',
        //'https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',

        'plugins/jquery/jquery.min.js',
        'plugins/jquery-ui/jquery-ui.min.js',
        'plugins/bootstrap/js/bootstrap.bundle.min',
        'plugins/chart.js/Chart.min.js',
        'plugins/sparklines/sparkline.js',
        'plugins/jqvmap/jquery.vmap.min.js ',
        'plugins/jqvmap/maps/jquery.vmap.usa.js',
        'plugins/jquery-knob/jquery.knob.min.js',
        'plugins/moment/moment.min.js',
        'plugins/daterangepicker/daterangepicker.js',
        'plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js',
        'plugins/summernote/summernote-bs4.min.js',
        'plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js',
        'plugins/jquery-mousewheel/jquery.mousewheel.js',
        'plugins/raphael/raphael.min.js',
        'plugins/jquery-mapael/jquery.mapael.min.js',
        'plugins/jquery-mapael/maps/usa_states.min.js',

        //chart float
        'plugins/flot/jquery.flot.js',
        'plugins/flot-old/jquery.flot.resize.min.js',
        'plugins/flot-old/jquery.flot.pie.min.js',

        //chart inline
        'plugins/sparkline/jquery.sparkline.min.js',

        //'dist/js/pages/dashboard.js',
        //'dist/js/pages/dashboard2.js',
        //'dist/js/pages/dashboard3.js',
        'dist/js/demo.js',

        //ui sliders
        'plugins/ion-rangeslider/js/ion.rangeSlider.min.js',
        'plugins/bootstrap-slider/bootstrap-slider.min.js',

        //ui modals
        'plugins/sweetalert2/sweetalert2.min.js',
        'plugins/toastr/toastr.min.js',

        //forms general
        'plugins/bs-custom-file-input/bs-custom-file-input.min.js',

        //forms advanced
        'plugins/select2/js/select2.full.min.js',
        'plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js',
        'plugins/inputmask/min/jquery.inputmask.bundle.min.js',
        'plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js',
        'plugins/bootstrap-switch/js/bootstrap-switch.min.js',

        //editors Trumbowyg
        'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.20.0/trumbowyg.min.js',

        //tables data tables
        'plugins/datatables/jquery.dataTables.js',
        'plugins/datatables-bs4/js/dataTables.bootstrap4.js',

        //tables jsgrid
        'plugins/jsgrid/demos/db.js',
        'plugins/jsgrid/jsgrid.min.js',

        //calendar
        'plugins/fullcalendar/main.min.js',
        'plugins/fullcalendar-daygrid/main.min.js',
        'plugins/fullcalendar-timegrid/main.min.js',
        'plugins/fullcalendar-interaction/main.min.js',
        'plugins/fullcalendar-bootstrap/main.min.js',

        //gallery
        'plugins/ekko-lightbox/ekko-lightbox.min.js',
        'plugins/filterizr/jquery.filterizr.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
