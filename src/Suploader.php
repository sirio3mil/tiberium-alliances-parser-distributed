<?php
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use limitium\zmq\FastBox;

error_reporting(E_ALL);

$fastbox = new FastBox("tcp://localhost:5556", "tcp://*:5557", 100, 2500, null, false);

$fastbox->transfer();