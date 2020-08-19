<?php

namespace app\modules\bot\components\helpers;

class ExternalLink
{
    public static function getOSMLink($latitude, $longitude)
    {
        return "https://www.openstreetmap.org/#map=14/$latitude/$longitude";
    }

    public static function getBotLink()
    {
        return 'https://t.me/opensourcewebsite_bot';
    }
}
