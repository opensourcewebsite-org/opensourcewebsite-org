<?php
/* @var $this \yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = Yii::t('menu', 'Moqups');
?>
<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Pages
        </h3>
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <a href="<?= Yii::$app->urlManager->createUrl(['moqup/design-add']) ?>"><button type="button" class="btn btn-outline-success" data-toggle="tooltip" data-placement="top" title="Create New"><i class="fa fa-plus"></i></button></a>
<<<<<<< HEAD
            </li>
=======
            </li> 
>>>>>>> 8fad682aacc7fa7af54920cdd46f6f719dde83ef
            <?php
            $all_active = '';
            $your_active = '';
            if ($viewMode) {
                $your_active = ' active';
            } else {
                $all_active = ' active';
            }
            ?>
            <li class="nav-item">
                <a class="nav-link show<?= $all_active; ?>" href="<?= Yii::$app->urlManager->createUrl(['moqup/design-list']) ?>">All <span class="badge badge-light ml-1"><?= count($moqups); ?></span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $your_active; ?>" href="<?= Url::to(['moqup/design-list/', 'viewMode' => '1']); ?>">Your <span class="badge badge-light ml-1"><?= count($your_moqups); ?></span></a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover" id="list_table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>User</th>
                        <th>Date</th>
                        <th data-orderable="false"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($viewMode) {
                        if (count($your_moqups) > 0) {
                            foreach ($your_moqups as $moqup) {
                                ?>
                                <tr>
                                    <td><?= $moqup['title']; ?></td>
                                    <td><?= $moqup['username']; ?></td>
                                    <td>
                                        <?php
                                        $formatter = \Yii::$app->formatter;
                                        $moqup_date = $formatter->asDate($moqup['created_at']);
                                        echo $moqup_date;
                                        ?>
                                    </td>
                                    <td class="text-right"><a href="<?= Url::to(['moqup/design-view/', 'id' => $moqup['id']]); ?>" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a><a href="<?= Url::to(['moqup/design-edit/', 'id' => $moqup['id']]); ?>"> <button type="button" class="btn btn-sm btn-outline-secondary"  data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></button></a> <button type="button" class="btn btn-sm btn-outline-danger"  data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                    } else {
                        if (count($moqups) > 0) {
                            foreach ($moqups as $moqup) {
                                ?>
                                <tr>
                                    <td><?= $moqup['title']; ?></td>
                                    <td><?= $moqup['username']; ?></td>
                                    <td>
                                        <?php
                                        $moqup_date_time = strtotime($moqup['created_at']);
                                        $moqup_date = date('d-m-Y', $moqup_date_time);
                                        echo $moqup_date;
                                        ?>
                                    </td>
                                    <td class="text-right"><a href="<?= Url::to(['moqup/design-view/', 'id' => $moqup['id']]); ?>" target="_blank"><button type="button" class="btn btn-sm btn-outline-primary"  data-toggle="tooltip" data-placement="top" title="Preview"><i class="fas fa-external-link-alt"></i></button></a></td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>