<?php
require_once "Ventilator.php";

$server = new Ventilator(true);
$server->bind("tcp://*:5555");
$server->listen();
