<?php
/**
 * @var \yii\web\View $this
 * @var array $center
 */
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('app', 'Location') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="map-container">

    </div>

    <div class="current-position-div">
        <p>Position: <span id="current-position-span"><?=$center['lat']?>,<?=$center['lng']?></span></p>
    </div>
</div>
<div class="modal-footer justify-content-between">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="location-save-changes" type="button" class="btn btn-primary" data-dismiss="modal">Save
        changes
    </button>
</div>

<?php
$js = <<<JS
function map_init(){
var map = L.map('leaflet', {});
L.tileLayer('https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', {"attribution":"Tiles Courtesy of <a href=\"//www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"//developer.mapquest.com/content/osm/mq_logo.png\">, Map data &copy; <a href=\"//openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"//creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>","subdomains":[1,2,3,4]}).addTo(map);
map.on('load',
                                    function (e) {
                                        window.positionMarker = new L.Marker([e.sourceTarget.getCenter().lat, e.sourceTarget.getCenter().lng]).addTo(map);
                                        L.control.locate().addTo(e.sourceTarget);
                                        $(document).on('shown.bs.modal','#modal-xl',  function(){
                                            setTimeout(function() {
                                                e.sourceTarget.invalidateSize();
                                            }, 1);
                                        });
                                    }
                                );
map.on('click',
                                function(e) {
                                    if (window.positionMarker) {
                                        window.positionMarker.setLatLng(e.latlng);
                                        position = e.latlng;
                                        $('#current-position-span').text(e.latlng.lat+','+e.latlng.lng);
                                    }
                                }
                                );
map.setView([51.508,-0.11], 13);}
map_init();
JS;

$this->registerJs($js);
