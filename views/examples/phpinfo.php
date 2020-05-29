<?php
use app\assets\PHPInfoAsset;

$this->registerAssetBundle(PHPInfoAsset::class);

$this->title = Yii::t('app', 'PHPinfo()');
$this->params['breadcrumbs'][] = $this->title;

$JS = <<<JS
    "use strict"
    $(document).ready(function(){

        let PI_styles       = $(".phpinfo > style").text();
        let array_PI_styles = PI_styles.split(/\\n/);
        let result_styles   = '';
        for (let styles_string of array_PI_styles)
        {
            if (styles_string.length == 0)
            {
                continue;
            }
            else if (styles_string.startsWith('body'))
            {
                result_styles += styles_string.replace('body', '.phpinfo');
            }
            else
            {
                result_styles += '.phpinfo ' + styles_string;
            }
        }
        $('.phpinfo > style').html(result_styles);

    })
JS;
$this->registerJs($JS);
?>


<div class="phpinfo">
    <?php phpinfo(); ?>
</div>
