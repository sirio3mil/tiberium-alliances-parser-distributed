<?php
require_once "lib/0MQ/0MQ/Worker.php";

$wrk = new Worker("tcp://localhost:5555", true);

$wrk->setExecuter(function ($data) {
    sleep(5);
    return "done $data";
});

$wrk->work();


