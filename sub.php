<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once "lib/0MQ/0MQ/Subscriber.php";

$fastbox = new Subscriber("tcp://192.168.123.1:5556", 2500, 0);

$fastbox->setListner(function ($data, $timestamp) {
    if (!$timestamp) {
        print_r("Empty timestamp!" . PHP_EOL);
    }
    if (!$data) {
        print_r("Empty data!" . PHP_EOL);
    }

});
$fastbox->setMisser(function () {

});
$fastbox->listen();
