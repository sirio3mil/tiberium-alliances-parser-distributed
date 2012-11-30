<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once "lib/0MQ/0MQ/Ventilator.php";
require_once "Generator.php";

$context = new ZMQContext();
$publisher = new ZMQSocket($context, ZMQ::SOCKET_PUB);
$bind = "tcp://*:5556";
$publisher->bind($bind);

$server = new Ventilator(true, 5000);
$server->bind("tcp://*:5555");
$generator = new Generator();

$server->setGenerator(function () use($generator)
{
    Timer::set("session");
    $server = $generator->nextServer();
    print_r("Session get time: " . Timer::get("session") . PHP_EOL);
    if (!$server) {
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

$server->setResponder(function ($data) use($generator, $publisher)
{
    $data = (array)json_decode($data);
    switch ($data["status"]) {
        case 1:
            print_r("Done :{$data["Id"]}" . PHP_EOL);
            $publisher->send($data["data"]);
            break;
        case 2:
            print_r("Fail :{$data["Id"]}" . PHP_EOL);
            break;
        case 3:
            $generator->reloadSession($data["Id"]);
            print_r("Session fails for {$data["Id"]}" . PHP_EOL);
            break;
        default:
            print_r("Invalid status {$data["status"]}" . PHP_EOL);
    }
});

$server->listen();
