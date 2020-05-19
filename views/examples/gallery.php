<?php

$this->registerAssetBundle(\app\assets\widgets\GalleryAsset::class);

$this->title = Yii::t('app', 'Gallery');
$this->params['breadcrumbs'][] = $this->title;

$JS = <<<JS
    $(function () {
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        $('.filter-container').filterizr({gutterPixels: 3});
        $('.btn[data-filter]').on('click', function() {
            $('.btn[data-filter]').removeClass('active');
            $(this).addClass('active');
        });
    })
JS;
$this->registerJs($JS);
?>
<!DOCTYPE html>
<html>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="card-title">
                            FilterizR Gallery with Ekko Lightbox
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <div class="btn-group w-100 mb-2">
                                <a class="btn btn-info active" href="javascript:void(0)" data-filter="all"> All
                                    items </a>
                                <a class="btn btn-info" href="javascript:void(0)" data-filter="1"> Category 1
                                    (WHITE) </a>
                                <a class="btn btn-info" href="javascript:void(0)" data-filter="2"> Category 2
                                    (BLACK) </a>
                                <a class="btn btn-info" href="javascript:void(0)" data-filter="3"> Category 3
                                    (COLORED) </a>
                            </div>
                            <div class="mb-2">
                                <a class="btn btn-secondary" href="javascript:void(0)" data-shuffle> Shuffle items </a>
                                <div class="float-right">
                                    <select class="custom-select" style="width: auto;" data-sortOrder>
                                        <option value="index"> Sort by Position</option>
                                        <option value="sortData"> Sort by Custom Data</option>
                                    </select>
                                    <div class="btn-group">
                                        <a class="btn btn-default" href="javascript:void(0)" data-sortAsc>
                                            Ascending </a>
                                        <a class="btn btn-default" href="javascript:void(0)" data-sortDesc>
                                            Descending </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="filter-container p-0 row">
                                <div class="filtr-item col-sm-2" data-category="1" data-sort="white sample">
                                    <a href="https://via.placeholder.com/1200/FFFFFF.png?text=1" data-toggle="lightbox" data-title="sample 1 - white">
                                        <img src="https://via.placeholder.com/300/FFFFFF?text=1" class="img-fluid mb-2" alt="white sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="2" data-sort="black sample">
                                    <a href="https://via.placeholder.com/1200/000000.png?text=2" data-toggle="lightbox" data-title="sample 2 - black">
                                        <img src="https://via.placeholder.com/300/000000?text=2" class="img-fluid mb-2" alt="black sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="3" data-sort="red sample">
                                    <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=3" data-toggle="lightbox" data-title="sample 3 - red">
                                        <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=3" class="img-fluid mb-2" alt="red sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="4" data-sort="red sample">
                                    <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=4" data-toggle="lightbox" data-title="sample 4 - red">
                                        <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=4" class="img-fluid mb-2" alt="red sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="5" data-sort="black sample">
                                    <a href="https://via.placeholder.com/1200/000000.png?text=5" data-toggle="lightbox" data-title="sample 5 - black">
                                        <img src="https://via.placeholder.com/300/000000?text=5" class="img-fluid mb-2" alt="black sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="6" data-sort="white sample">
                                    <a href="https://via.placeholder.com/1200/FFFFFF.png?text=6" data-toggle="lightbox" data-title="sample 6 - white">
                                        <img src="https://via.placeholder.com/300/FFFFFF?text=6" class="img-fluid mb-2" alt="white sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="7" data-sort="white sample">
                                    <a href="https://via.placeholder.com/1200/FFFFFF.png?text=7" data-toggle="lightbox" data-title="sample 7 - white">
                                        <img src="https://via.placeholder.com/300/FFFFFF?text=7" class="img-fluid mb-2" alt="white sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="8" data-sort="black sample">
                                    <a href="https://via.placeholder.com/1200/000000.png?text=8" data-toggle="lightbox" data-title="sample 8 - black">
                                        <img src="https://via.placeholder.com/300/000000?text=8" class="img-fluid mb-2" alt="black sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="9" data-sort="red sample">
                                    <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=9" data-toggle="lightbox" data-title="sample 9 - red">
                                        <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=9" class="img-fluid mb-2" alt="red sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="10" data-sort="white sample">
                                    <a href="https://via.placeholder.com/1200/FFFFFF.png?text=10" data-toggle="lightbox" data-title="sample 10 - white">
                                        <img src="https://via.placeholder.com/300/FFFFFF?text=10" class="img-fluid mb-2" alt="white sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="11" data-sort="white sample">
                                    <a href="https://via.placeholder.com/1200/FFFFFF.png?text=11" data-toggle="lightbox" data-title="sample 11 - white">
                                        <img src="https://via.placeholder.com/300/FFFFFF?text=11" class="img-fluid mb-2" alt="white sample"/>
                                    </a>
                                </div>
                                <div class="filtr-item col-sm-2" data-category="12" data-sort="black sample">
                                    <a href="https://via.placeholder.com/1200/000000.png?text=12" data-toggle="lightbox" data-title="sample 12 - black">
                                        <img src="https://via.placeholder.com/300/000000?text=12" class="img-fluid mb-2" alt="black sample"/>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="card-title">
                            Ekko Lightbox
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=1" data-toggle="lightbox" data-title="sample 1 - white" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FFFFFF?text=1" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/000000.png?text=2" data-toggle="lightbox" data-title="sample 2 - black" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/000000?text=2" class="img-fluid mb-2" alt="black sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=3" data-toggle="lightbox" data-title="sample 3 - red" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=3" class="img-fluid mb-2" alt="red sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=4" data-toggle="lightbox" data-title="sample 4 - red" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=4" class="img-fluid mb-2" alt="red sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/000000.png?text=5" data-toggle="lightbox" data-title="sample 5 - black" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/000000?text=5" class="img-fluid mb-2" alt="black sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=6" data-toggle="lightbox" data-title="sample 6 - white" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FFFFFF?text=6" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=7" data-toggle="lightbox" data-title="sample 7 - white" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FFFFFF?text=7" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/000000.png?text=8" data-toggle="lightbox" data-title="sample 8 - black" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/000000?text=8" class="img-fluid mb-2" alt="black sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=9" data-toggle="lightbox" data-title="sample 9 - red" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=9" class="img-fluid mb-2" alt="red sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=10" data-toggle="lightbox" data-title="sample 10 - white" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FFFFFF?text=10" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=11" data-toggle="lightbox" data-title="sample 11 - white" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/FFFFFF?text=11" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="https://via.placeholder.com/1200/000000.png?text=12" data-toggle="lightbox" data-title="sample 12 - black" data-gallery="gallery">
                                    <img src="https://via.placeholder.com/300/000000?text=12" class="img-fluid mb-2" alt="black sample"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</a>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</html>
