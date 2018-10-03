<?php 
//To avoid render the debug toolbar
if (class_exists('yii\debug\Module')) {
    $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
}

$this->registerCss('body {
    background-color: rgba(0,0,0,0);
}');
?>
<div id="prev-content"></div>