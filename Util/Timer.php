<?php
class Timer
{
    private static $timers = array();

    public static function set($timer)
    {
        self::$timers[$timer] = microtime(true);
    }

    public static function get($timer)
    {
        return microtime(true) - self::$timers[$timer];
    }
}
