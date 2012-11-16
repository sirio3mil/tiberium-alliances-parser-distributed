<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";

class Generator
{
    public static $servers;
    public static $keys = array();
    public static $authorizator;
    public static $reloadSession = true;

    public static function nextServer()
    {
        if (sizeof(self::$keys) == 0) {
            self::$keys = array_keys(self::$servers);
        }
        while (!is_numeric($id = array_shift(self::$keys))) {
        }
        return self::$servers[$id];

    }

    public static function getSession()
    {
        $session = self::$authorizator->getSession(self::$reloadSession);
        self::$reloadSession = false;
        return $session;
    }
}

Generator::$authorizator = new CCAuth("limitium@gmail.com", "qweqwe123");
Generator::$servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";


require_once "lib/0MQ/0MQ/Ventilator.php";

$server = new Ventilator(true, 5000);
$server->bind("tcp://*:5555");

$server->setGenerator(function ()
{
    $server = Generator::nextServer();
    Timer::set("session");
    $server["session"] = Generator::getSession();
    print_r("Session get time: " . Timer::get("session") . PHP_EOL);
    if (!$server["session"]) {
        return false;
    }
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
    $data = (array)json_decode($data);
    switch ($data["status"]) {
        case 1:
            print_r("Done :{$data["Id"]}" . PHP_EOL);
            break;
        case 2:
            print_r("Fail :{$data["Id"]}" . PHP_EOL);
            break;
        case 3:
            Generator::$reloadSession = true;
            print_r("Session fails" . PHP_EOL);
            break;
        default:
            print_r("Invalid status {$data["status"]}" . PHP_EOL);
    }
});

$server->listen();
