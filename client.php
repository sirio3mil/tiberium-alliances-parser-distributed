<?php
require_once "zmsg.php";

// Prepare our context and sockets
$context = new ZMQContext();

// Bind cloud frontend to endpoint
$cloudfe = $context->getSocket(ZMQ::SOCKET_DEALER);
$cloudfe->connect("tcp://localhost:5556");

$data = json_encode(array(
"world"=>666,
"data"=>"asdasdasd1231231312qwdasdasd"
));
var_dump($data);
var_dump($cloudfe->send($data));
sleep(15);