<?php
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use limitium\zmq\Publisher;
use limitium\zmq\Ventilator;
use limitium\TAPD\Generator;


$context = new \ZMQContext();

$publisher = new Publisher("tcp://*:5556", $context, false);

$ventilator = new Ventilator("tcp://*:5555", 5000, $context, false);
$generator = new Generator();

$ventilator->setGenerator(function () use ($generator) {
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

$ventilator->setResponder(function ($data) use ($generator, $publisher) {
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
