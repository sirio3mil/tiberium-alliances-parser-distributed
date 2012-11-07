<?php

// Prepare our context and sockets
$context = new ZMQContext();

// Bind cloud frontend to endpoint
$cloudfe = $context->getSocket(ZMQ::SOCKET_DEALER);
$cloudfe->connect("tcp://localhost:5556");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
$cloudfe->send("asd");
sleep(15);