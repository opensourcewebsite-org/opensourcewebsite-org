<?php 

if (isset($error)) {
    echo '<b>' . Yii::t('bot', 'Errors') . '</b>:<br/>';

    foreach ($error as $error_line) {
       echo  '<i>' . Yii::t('bot', $error_line) . '</i><br/>';
    }
}

?>