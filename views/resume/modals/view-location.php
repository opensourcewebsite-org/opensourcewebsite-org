<?php

declare(strict_types=1);

use app\models\Resume;
use yii\web\View;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use yii\web\JsExpression;
use dosamigos\leaflet\widgets\Map;

/**
 * @var $this View
 * @var $type string
 * @var $model Resume
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
            'attribution' => 'Tiles Courtesy of <a href="//www.mapquest.com/" target="_blank">MapQuest</a> ' .
                '<img src="//developer.mapquest.com/content/osm/mq_logo.png">, ' .
                'Map data &copy; <a href="//openstreetmap.org">OpenStreetMap</a> contributors, <a href="//creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
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
