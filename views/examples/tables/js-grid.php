<?php
$this->registerAssetBundle(\app\assets\AdminLteContributingAsset::class);

$this->title = 'jsGrid';
$this->params['breadcrumbs'][] = $this->title;

$JS = <<<JS
  $(function () {
    $("#jsGrid1").jsGrid({
        height: "100%",
        width: "100%",

        sorting: true,
        paging: true,

        data: db.clients,

        fields: [
            { name: "Name", type: "text", width: 150 },
            { name: "Age", type: "number", width: 50 },
            { name: "Address", type: "text", width: 200 },
            { name: "Country", type: "select", items: db.countries, valueField: "Id", textField: "Name" },
            { name: "Married", type: "checkbox", title: "Is Married" }
        ]
    });
  });

JS;
$this->registerJs($JS);
?>
<!DOCTYPE html>
<html>

<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">jsGrid</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div id="jsGrid1"></div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
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

