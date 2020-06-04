<?php

namespace app\modules\bot\components\helpers;

class ExternalLink
{
	public static function getOSMLink($latitude, $longitude)
	{
		return "https://www.openstreetmap.org/#map=14/$latitude/$longitude";
	}
}
