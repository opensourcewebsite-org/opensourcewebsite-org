<?php
declare(strict_types=1);

use yii\base\Model;
use yii\helpers\Html;

/** @var Model $model */
/** @var string $attribute */
/** @var string $id */
?>

    <div class="input-group d-flex mb-3 align-items-start">

        <?= Html::activeInput('text', $model, $attribute, ['class' => 'form-control flex-grow-1']) ?>

        <span class="input-group-append">
        <button type="button" class="btn btn-info btn-flat map-btn"
                data-toggle="modal" data-target="#<?=$id?>-modal">
                <?= Yii::t('app', 'Map') ?>
        </button>
    </span>
    </div>
    <div id="<?=$id?>-modal" class="cart-primary modal" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?= Yii::t('app', 'Location') ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div id="<?=$id?>-map-container" style="height: 500px;"></div>

                    <div class="current-position-div">
                        <p>
                            Position: <span id="<?=$id?>-current-position-span"></span>
                        </p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button id="<?=$id?>-location-save-changes" type="button" class="btn btn-primary" data-dismiss="modal">Save
                        changes
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php

$controlId = json_encode($id);
$js = <<<JS
$(document).ready(function() {
    const controlId = {$controlId};

    const defaultCenter = [51.505, -0.09];

    const map = L.map(controlId+'-map-container', {tap: false}).setView( defaultCenter, 2 );

    const lc = L.control.locate().addTo(map);

    L.tileLayer(
        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            "attribution": "Tiles Courtesy of <a href=\"//ты www.mapquest.com/\" target=\"_blank\">MapQuest</a> " +
             "<img src=\"//developer.mapquest.com/content/osm/mq_logo.png\">, Map data &copy; <a href=\"//openstreetmap.org\">OpenStreetMap</a> contributors, " +
              "<a href=\"//creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>",
            "subdomains": [1,2,3,4]
        }).addTo(map);

    const marker = L.marker(defaultCenter).addTo(map);

    const updatePosition = (latlng) => {
        marker.setLatLng(latlng);
        $('#'+controlId+'-current-position-span').text(latlng.lat+','+latlng.lng);
    }

    map.on('click', function(e) {
        updatePosition(e.latlng);
    });

    map.on('locationfound', function (e) {
        updatePosition(e.latlng);
        map.setView(e.latlng, 12);
        lc.stop();
    });

    $(document).on('click', '#'+controlId+'-location-save-changes', function() {
        if ( controlId ) {
            const latlng = marker.getLatLng();
            $('#'+controlId).val(latlng.lat+','+latlng.lng);
        }
    })

    const tryToParseCurrentLocation = (locStr) => {
        let [lat, lng] = locStr.split(',');
        if (lat && (lat = parseFloat(lat)) && lng && (lng = parseFloat(lng))) {
            return {lat: lat, lng: lng};
        }
        return false;
    }

    $(document).on('shown.bs.modal', '#'+controlId+'-modal', function () {
        map.invalidateSize();

        let currentLoc = false;
        if (controlId && (currentLoc = tryToParseCurrentLocation($('#'+controlId).val()))) {
            updatePosition(currentLoc);
            map.setView(currentLoc, 12);
        } else {
            lc.start();
        }
    });
})
JS;
$this->registerJs($js);
