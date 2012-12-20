<?php
error_reporting(E_ALL);

require_once "lib/0MQ/0MQ/FastBox.php";

$fastbox = new FastBox("tcp://192.168.123.1:5556", 0, 2500);
$fastbox->bind("tcp://*:5557");

$fastbox->transfer();