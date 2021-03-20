<?php

use yii\web\View;
use app\models\CurrencyExchangeOrder;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use yii\web\JsExpression;
use dosamigos\leaflet\widgets\Map;

/**
 * @var $this View
 * @var $model CurrencyExchangeOrder
 */
$this->title = Yii::t('app', 'Location of order: ' . $model->id);

?>
    <div class="modal-header">
        <h4 class="modal-title"><?= $this->title ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    </div>
    <div class="modal-body">

        <?php
        if ($type === 'sell') {
            $center = new LatLng(['lat' => $model->selling_location_lat ?: 51.508, 'lng' => $model->selling_location_lon ?: -0.11]);
        } else {
            $center = new LatLng(['lat' => $model->buying_location_lat ?: 51.508, 'lng' => $model->buying_location_lon ?: -0.11]);
        }
        $marker = new Marker([
            'latLng' => $center,
            'clientOptions' => [
                'draggable' => true,
            ],
            'clientEvents' => [
                'dragend' => 'function(e) {
                                    var marker = e.target;
                                    position = marker.getLatLng();
                                }'
            ],
        ]);

        $tileLayer = new TileLayer([
            'urlTemplate' => 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'clientOptions' => [
                'attribution' => 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> ' .
                    '<img src="http://developer.mapquest.com/content/osm/mq_logo.png">, ' .
                    'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
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
