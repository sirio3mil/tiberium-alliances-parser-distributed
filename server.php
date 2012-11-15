<?php
require_once "lib/0MQ/0MQ/Ventilator.php";

$server = new Ventilator(true);
$server->bind("tcp://*:5555");

$server->setGenerator(function () {
    return mt_rand(1, 1000);
});

$server->setResponder(function ($data) {
    print_r("Got data:$data" . PHP_EOL);
});

$server->listen();
