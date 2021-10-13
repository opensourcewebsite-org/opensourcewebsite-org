<?php

declare(strict_types=1);

namespace app\helpers;

class LatLonHelper
{
    public static function generateRandomPoint($centre, $radius): array
    {
        $radius_earth = 3959; //miles

        //Pick random distance within $distance;
        $distance = lcg_value() * $radius;

        //Convert degrees to radians.
        $centre_rads = array_map('deg2rad', $centre);

        //First suppose our point is the north pole.
        //Find a random point $distance miles away
        $lat_rads = (pi() / 2) - $distance / $radius_earth;
        $lon_rads = lcg_value() * 2 * pi();

        //($lat_rads,$lng_rads) is a point on the circle which is
        //$distance miles from the north pole. Convert to Cartesian
        $x1 = cos($lat_rads) * sin($lon_rads);
        $y1 = cos($lat_rads) * cos($lon_rads);
        $z1 = sin($lat_rads);

        //Rotate that sphere so that the north pole is now at $centre.

        //Rotate in x axis by $rot = (pi()/2) - $centre_rads[0];
        $rot = (pi() / 2) - $centre_rads[0];
        $x2 = $x1;
        $y2 = $y1 * cos($rot) + $z1 * sin($rot);
        $z2 = -$y1 * sin($rot) + $z1 * cos($rot);

        //Rotate in z axis by $rot = $centre_rads[1]
        $rot = $centre_rads[1];
        $x3 = $x2 * cos($rot) + $y2 * sin($rot);
        $y3 = -$x2 * sin($rot) + $y2 * cos($rot);
        $z3 = $z2;

        //Finally convert this point to polar co-ords
        $lon_rads = atan2($x3, $y3);
        $lat_rads = asin($z3);

        return array_map('rad2deg', [$lat_rads, $lon_rads]);
    }

    public static function getCircleDistance(float $lat1, float $lon1, float $lat2, float $lon2)
    {
        $rad = M_PI / 180;

        return acos(sin($lat2*$rad) * sin($lat1*$rad) + cos($lat2*$rad) * cos($lat1*$rad) * cos($lon2*$rad - $lon1*$rad)) * 6371;// Kilometers
    }
}
