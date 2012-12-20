<?php
class Timer
{
    private static $timers = array();

    public static function set($timer)
    {
        self::$timers[$timer] = microtime(true) * 1000;
    }

    public static function get($timer)
    {
        return microtime(true) * 1000 - self::$timers[$timer];
    }
}
