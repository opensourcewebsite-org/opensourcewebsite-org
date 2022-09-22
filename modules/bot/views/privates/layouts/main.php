<?php 

if (isset($this->params['errors'])) {
    echo '<b>' . Yii::t('bot', 'Errors') . '</b>:<br/>';
    
    foreach ($this->params['errors'] as $error) {
        if (is_array($error)) {
            foreach ($error as $error_attr) {
                echo '<i>' . Yii::t('bot', $error_attr) . '</i><br/>';
            }
        }
        else {
            echo  '<i>' . Yii::t('bot', $error) . '</i><br/>';
        }
    }
}

?>
<br/>
<?= $content ?>