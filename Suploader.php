<?php
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Curler.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Util" . DIRECTORY_SEPARATOR . "Timer.php";
require_once "lib/0MQ/0MQ/FastBox.php";
require_once "lib/0MQ/0MQ/Subscriber.php";

$fastbox = new FastBox("tcp://192.168.123.1:5556", 0, 2500);
$fastbox->bind("tcp://*:5557");

$fastbox->transfer();