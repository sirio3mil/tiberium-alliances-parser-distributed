<?php
require_once "zmsg.php";

$context = new ZMQContext();
$be_workers = $context->getSocket(ZMQ::SOCKET_ROUTER);
$be_workers->bind("tcp://*:5555");

$workers = array();
$writeable = $readable = array();
while (true) {
    $poll = new ZMQPoll();

    $poll->add($be_workers, ZMQ::POLL_IN);
    $events = $poll->poll($readable, $writeable,1000);
    print_r($events);
    if ($events > 0) {
        $zmsg = new Zmsg($be_workers);
        $zmsg->recv();
//        print_r($zmsg->address());
        print_r($zmsg->body());
        $zmsg->body_set("t");
        $zmsg->send();
    }

}

