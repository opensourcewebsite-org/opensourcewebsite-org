<?php

declare(strict_types=1);

use app\models\AdOffer;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\widgets\Map;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var View $this
 * @var AdOffer $model
 */

$this->title = Yii::t('app', 'Location');
?>
<div class="modal-header">
    <h4 class="modal-title"><?= $this->title ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<div class="modal-body">

    <?php

    $center = new LatLng(['lat' => $model->location_lat ?: 51.508, 'lng' => $model->location_lon ?: -0.11]);

    $marker = new Marker([
        'latLng' => $center,
        'clientOptions' => [
            'draggable' => true,
        ],
        'clientEvents' => [
            'dragend' => new JsExpression("
                            function(e) {
                              var marker = e.target;
                              position = marker.getLatLng();
                          }")
        ],
    ]);

    $tileLayer = new TileLayer([
        'urlTemplate' => 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'clientOptions' => [
            'attribution' => '© <a href="//www.openstreetmap.org/copyright" rel="nofollow noreferrer noopener" target="_blank">OpenStreetMap</a> contributors',
            'subdomains' => ['1', '2', '3', '4'],
        ],
    ]);

    $leaflet = new LeafLet([
        'center' => $center,
        'clientEvents' => [
            'load' => new JsExpression("
                                    function (e) {
                                        setTimeout(function() {
                                            e.sourceTarget.invalidateSize();
                                        }, 1);
                                    }
                                ")
        ]
    ]);

    $leaflet->addLayer($marker)->addLayer($tileLayer);

    echo Map::widget([
        'leafLet' => $leaflet,
        'options' => [
            'id' => 'leaflet',
            'style' => 'height:500px',
        ],
    ]);
    ?>

</div>
