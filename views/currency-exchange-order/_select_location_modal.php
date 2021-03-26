<?php

use app\assets\LeafletLocateControlAsset;
use dosamigos\leaflet\LeafLetAsset;

/**
 * @var \yii\web\View $this
 * @var array|false $center
 */

LeafLetAsset::register($this);
LeafletLocateControlAsset::register($this);

?>
    <div id="map-modal" class="cart-primary modal" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?= Yii::t('app', 'Location') ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div id="map-container" style="height: 500px;"></div>

                    <div class="current-position-div">
                        <p>
                            Position: <span id="current-position-span"></span>
                        </p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button id="location-save-changes" type="button" class="btn btn-primary" data-dismiss="modal">Save
                        changes
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php

$js = <<<JS
$(document).ready(function() {
    const defaultCenter = [51.505, -0.09];

    const map = L.map('map-container', {tap: false}).setView( defaultCenter, 2 );
    window.mymap = map;

    const lc = L.control.locate().addTo(map);

    L.tileLayer(
        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            "attribution": "Tiles Courtesy of <a href=\"//www.mapquest.com/\" target=\"_blank\">MapQuest</a> " +
             "<img src=\"//developer.mapquest.com/content/osm/mq_logo.png\">, Map data &copy; <a href=\"//openstreetmap.org\">OpenStreetMap</a> contributors, " +
              "<a href=\"//creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>",
            "subdomains": [1,2,3,4]
        }).addTo(map);

    const marker = L.marker(defaultCenter).addTo(map);

    const updatePosition = (latlng) => {
        marker.setLatLng(latlng);
        $('#current-position-span').text(latlng.lat+','+latlng.lng);
    }

    map.on('click', function(e) {
        updatePosition(e.latlng);
    });

    map.on('locationfound', function (e) {
        updatePosition(e.latlng);
        map.setView(e.latlng, 12);
        lc.stop();
    });

    $(document).on('click', '#location-save-changes', function() {
        if ( window.currencyExchangeLocationTargetField ) {
            const latlng = marker.getLatLng();
            window.currencyExchangeLocationTargetField.val(latlng.lat+','+latlng.lng);
        }
    })

    const tryToParseCurrentLocation = (locStr) => {
        let [lat, lng] = locStr.split(',');
        if (lat && (lat = parseFloat(lat)) && lng && (lng = parseFloat(lng))) {
            return {lat: lat, lng: lng};
        }
        return false;
    }

    $(document).on('shown.bs.modal', function () {

        map.invalidateSize();

        let currentLoc = false;
        if (window.currencyExchangeLocationTargetField &&
        (currentLoc = tryToParseCurrentLocation(window.currencyExchangeLocationTargetField.val()))) {
            updatePosition(currentLoc);
            map.setView(currentLoc, 12);
        } else {
            lc.start();
        }

    });
})

JS;

$this->registerJs($js);
