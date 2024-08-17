<?php

namespace Seat\Kassie\Calendar\Helpers;

class SeatFittingPluginHelper
{
    public static function pluginIsAvailable()
    {
        return class_exists(self::$DOCTRINE_MODEL)
            && class_exists(self::$FITTING_MODEL);
    }

    public static $DOCTRINE_MODEL = "Denngarr\Seat\Fitting\Models\Doctrine";
    public static $FITTING_MODEL = "Denngarr\Seat\Fitting\Models\Fitting";
}