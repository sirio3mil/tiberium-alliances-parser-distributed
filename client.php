<?php
require_once "Worker.php";

$wrk = new Worker("tcp://localhost:5555", true);

while (1) {
    $msg = $wrk->recv();
    //do hard work;
    $wrk->send(W_RESPONSE);
}


