<?php
error_reporting(E_ALL);
class Generator
{
    public static $servers;
    public static $keys = array();

    public static function nextServer()
    {
        if (sizeof(self::$keys) == 0) {
            self::$keys = array_keys(self::$servers);
        }
        while (!is_numeric($id = array_shift(self::$keys))) {
        }
        return self::$servers[$id];

    }
}

Generator::$servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";


require_once "lib/0MQ/0MQ/Ventilator.php";

$server = new Ventilator(true);
$server->bind("tcp://*:5555");

$server->setGenerator(function ()
{
    $server = Generator::nextServer();
    unset($server["AcceptNewPlayer"]);
    unset($server["Description"]);
    unset($server["StartTime"]);
    unset($server["Language"]);
    unset($server["Timezone"]);
    unset($server["u"]);
    unset($server["p"]);
    return json_encode($server);
});

$server->setResponder(function ($data)
{
    print_r("Got data:$data" . PHP_EOL);
});

$server->listen();
