<?php

namespace Seat\Kassie\Calendar\Helpers;

class SeatFittingPluginHelper
{
    public static function pluginIsAvailable(): bool
    {
        return (class_exists(self::$OLD_DOCTRINE_MODEL)
                && class_exists(self::$OLD_FITTING_MODEL))
            || (class_exists(self::$DOCTRINE_MODEL)
                && class_exists(self::$FITTING_MODEL));
    }

    public static function isOldVersion(): bool
    {
        return (class_exists(self::$OLD_DOCTRINE_MODEL)
            && class_exists(self::$OLD_FITTING_MODEL));
    }

    protected static string $OLD_DOCTRINE_MODEL = "Denngarr\Seat\Fitting\Models\Doctrine";
    protected static string $OLD_FITTING_MODEL = "Denngarr\Seat\Fitting\Models\Fitting";

    protected static string $DOCTRINE_MODEL = "CryptaTech\Seat\Fitting\Models\Doctrine";

    protected static string $FITTING_MODEL = "CryptaTech\Seat\Fitting\Models\Fitting";

    public static function getOperation($doctrine_id)
    {
        if (self::isOldVersion()) {
            return self::$OLD_DOCTRINE_MODEL::where('id', $doctrine_id)->first();
        } else {
            return self::$DOCTRINE_MODEL::where('id', $doctrine_id)->first();
        }
    }

    public static function listDoctrines()
    {
        if (self::isOldVersion()) {
            return self::$OLD_DOCTRINE_MODEL::all();
        } else {
            return self::$DOCTRINE_MODEL::all();
        }
    }

    public static function hasDoctrines(): bool
    {
        if (self::isOldVersion()) {
            return self::$OLD_DOCTRINE_MODEL::count() > 0;
        } else {
            return self::$DOCTRINE_MODEL::count() > 0;
        }
    }

    public static function generateDoctrineUrl($doctrine_id): string
    {
        if (self::isOldVersion()) {
            return route('fitting.doctrineviewdetails', ['id' => $doctrine_id]);
        } else {
            return route('fitting.doctrineviewdetails', ['doctrine_id' => $doctrine_id]);
        }
    }
}