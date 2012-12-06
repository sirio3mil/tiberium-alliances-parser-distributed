<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CCAuth" . DIRECTORY_SEPARATOR . "CCAuth.php";
require_once "lib/0MQ/0MQ/Ventilator.php";
require_once "lib/0MQ/0MQ/Publisher.php";
require_once "Generator.php";

$context = new ZMQContext();

$publisher = new Publisher(false, $context);
$publisher->bind("tcp://*:5556");

$ventilator = new Ventilator(false, 5000, $context);
$ventilator->bind("tcp://*:5555");
$generator = new Generator();

$ventilator->setGenerator(function () use($generator)
{
    Timer::set("session");
    $server = $generator->nextServer();
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
    print_r("Start :{$server["Id"]}" . PHP_EOL);
    return json_encode($server);
});

$ventilator->setResponder(function ($data) use($generator, $publisher)
{
    $id = intval(substr($data, 0, 3));
    $status = intval(substr($data, 3, 2));
    $data = substr($data, 5);
    switch ($status) {
        case 1:
            print_r("Done :{$id}" . PHP_EOL);
            $publisher->send(sprintf("%03s", $id) . $data);
            break;
        case 2:
            print_r("Fail :{$id}" . PHP_EOL);
            break;
        case 3:
            $generator->reloadSession($id);
            print_r("Session fails for {$id}" . PHP_EOL);
            break;
        default:
            print_r("Invalid status {$status}" . PHP_EOL);
    }
});

$ventilator->listen();
