<?php 

if (isset($this->params['errors'])) {
    echo '<b>' . Yii::t('bot', 'Errors') . '</b>:<br/>';
    
    array_walk_recursive($this->params['errors'], function($error) { echo  '<i>' . Yii::t('bot', $error) . '</i><br/>'; });

}

?>
<br/>
<?= $content ?>